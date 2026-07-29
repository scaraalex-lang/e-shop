<?php

namespace Modules\Commerce\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Enums\Occasione;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Enums\StatoPagamento;

class Ordine extends Model
{
    use SoftDeletes;

    protected $table = 'ordini';

    protected $fillable = [
        'numero', 'user_id', 'agenzia_id',
        'metodo_pagamento',
        'totale_pieno', 'totale_merce', 'spedizione', 'totale',
        'consegna_nome', 'consegna_telefono', 'consegna_indirizzo',
        'consegna_cap', 'consegna_citta', 'consegna_provincia', 'note',
        'richiede_lavorazione', 'occasione', 'numero_anniversario',
    ];

    protected $casts = [
        'stato' => StatoOrdine::class,
        'metodo_pagamento' => MetodoPagamento::class,
        'stato_pagamento' => StatoPagamento::class,
        'occasione' => Occasione::class,
        'pagato_at' => 'datetime',
        'totale_pieno' => 'integer',
        'totale_merce' => 'integer',
        'spedizione' => 'integer',
        'totale' => 'integer',
        'richiede_lavorazione' => 'boolean',
        'numero_anniversario' => 'integer',
    ];

    protected $attributes = [
        'stato' => 'nuovo',
        'stato_pagamento' => 'in_attesa',
    ];

    public function getRouteKeyName(): string
    {
        return 'numero';
    }

    public function righe(): HasMany
    {
        return $this->hasMany(RigaOrdine::class)->orderBy('id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function agenzia(): BelongsTo
    {
        return $this->belongsTo(Agenzia::class);
    }

    /**
     * Chi può considerare questo ordine "suo" per lavorarci: chi l'ha
     * comprato, lo staff (lavora su qualunque pratica), o un altro referente
     * della stessa agenzia — un ordine B2B è dell'agenzia, non del singolo
     * login che l'ha creato ("un login per agenzia" è tipico, non garantito).
     */
    public function diChi(User $utente): bool
    {
        return $this->user_id === $utente->id
            || $utente->eStaff()
            || ($this->agenzia_id !== null && $utente->agenzia?->id === $this->agenzia_id);
    }

    public function scopeDi(Builder $query, User $utente): Builder
    {
        return $query->where('user_id', $utente->id);
    }

    /**
     * Il prossimo numero d'ordine dell'anno: MEM-2026-0001.
     *
     * Il conteggio è dentro una transazione con lock: due checkout nello
     * stesso istante non devono ottenere lo stesso numero.
     */
    public static function prossimoNumero(?int $anno = null): string
    {
        $anno ??= (int) date('Y');

        $ultimo = DB::table('ordini')
            ->where('numero', 'like', "MEM-{$anno}-%")
            ->lockForUpdate()
            ->orderByDesc('numero')
            ->value('numero');

        $progressivo = $ultimo ? ((int) substr($ultimo, -4)) + 1 : 1;

        return sprintf('MEM-%d-%04d', $anno, $progressivo);
    }

    /**
     * Promemoria leggero, solo un suggerimento mostrato dopo aver creato
     * l'ordine — nessun automatismo, nessuna nuova pratica generata.
     *
     * Preferisce `occasione`, scelta esplicitamente dall'agenzia (il
     * servizio flaggato nel carrello): un Funerale suggerisce il Trigesimo
     * fra 30 giorni, un Trigesimo suggerisce l'Anniversario fra un anno, un
     * Anniversario non suggerisce nulla di automatico (il prossimo non è
     * deducibile senza sapere quale numero sarà). Per gli ordini vecchi
     * senza il campo, resta l'euristica sulla categoria degli articoli
     * (unico segnale che esisteva prima di questo campo): un Kit Trigesimo
     * comprato per questo defunto suggerisce l'Anniversario, qualsiasi altro
     * ordine legato a un defunto suggerisce il Trigesimo.
     */
    public function prossimaScadenzaSuggerita(): ?array
    {
        if (! $this->defunto_id) {
            return null;
        }

        if ($this->occasione !== null) {
            return match ($this->occasione) {
                Occasione::Funerale => ['etichetta' => 'Trigesimo', 'quando' => $this->created_at->copy()->addDays(30)],
                Occasione::Trigesimo => ['etichetta' => 'Anniversario', 'quando' => $this->created_at->copy()->addYear()],
                Occasione::Anniversario => null,
            };
        }

        $haKitTrigesimo = $this->righe->contains(
            fn (RigaOrdine $riga) => $riga->product?->category?->slug === 'articoli-trigesimali'
        );

        return $haKitTrigesimo
            ? ['etichetta' => 'Anniversario', 'quando' => $this->created_at->copy()->addYear()]
            : ['etichetta' => 'Trigesimo', 'quando' => $this->created_at->copy()->addDays(30)];
    }

    public function risparmio(): int
    {
        return $this->totale_pieno - $this->totale_merce;
    }

    public function haSconti(): bool
    {
        return $this->risparmio() > 0;
    }

    public function pezzi(): int
    {
        return (int) $this->righe->sum('quantita');
    }

    /** Le righe che devono passare dalla lavorazione fotografica. */
    public function righeDaLavorare(): iterable
    {
        return $this->righe->where('richiede_foto', true);
    }

    /**
     * L'ordine è ancora aperto: è la condizione che tiene in piedi l'accesso
     * del cliente alla lavorazione e, in seguito, la validità del link di
     * approvazione mandato alla famiglia.
     */
    public function aperto(): bool
    {
        return ! $this->stato->concluso();
    }

    /**
     * Il cliente può entrare nella lavorazione fotografica di questo ordine.
     *
     * È la condizione che apre gli editor a chi ha comprato: non "sei
     * un'agenzia", ma "hai un ordine aperto che ha bisogno di questa
     * lavorazione". Vale identica per il privato e per l'agenzia.
     */
    public function lavorazioneApribile(): bool
    {
        return $this->richiede_lavorazione
            && $this->aperto()
            && $this->stato !== StatoOrdine::Nuovo;
    }

    public function registraPagamento(string $riferimento): void
    {
        $this->forceFill([
            'stato_pagamento' => StatoPagamento::Pagato,
            'pagato_at' => Carbon::now(),
            'riferimento_pagamento' => $riferimento,
        ])->save();
    }

    public function segnaPagamentoFallito(): void
    {
        $this->forceFill(['stato_pagamento' => StatoPagamento::Fallito])->save();
    }

    public function passaA(StatoOrdine $stato): void
    {
        $this->forceFill(['stato' => $stato])->save();
    }

    /** Lo staff segna la spedizione: corriere e tracking insieme al passaggio di stato. */
    public function spedisci(string $corriere, string $trackingNumero): void
    {
        $this->forceFill(['corriere' => $corriere, 'tracking_numero' => $trackingNumero])->save();
        $this->passaA(StatoOrdine::Spedito);
    }

    /**
     * Dove va l'ordine appena confermato: in lavorazione se c'è una foto da
     * preparare, altrimenti dritto in produzione.
     */
    public function avvia(): void
    {
        $this->passaA($this->richiede_lavorazione ? StatoOrdine::InLavorazione : StatoOrdine::InProduzione);
    }

    /** Le tappe del tracciamento, con quella raggiunta. */
    public function percorso(): array
    {
        $tappe = StatoOrdine::percorso($this->richiede_lavorazione);
        $indice = array_search($this->stato, $tappe, true);

        return array_map(fn ($tappa, $i) => [
            'stato' => $tappa,
            'fatta' => $indice !== false && $i <= $indice,
            'attuale' => $tappa === $this->stato,
        ], $tappe, array_keys($tappe));
    }
}
