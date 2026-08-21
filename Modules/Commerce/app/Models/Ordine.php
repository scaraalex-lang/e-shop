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
        'totale_pieno', 'totale_merce', 'spedizione', 'totale', 'crediti_usati',
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
        'fattura_emessa_at' => 'datetime',
        'totale_pieno' => 'integer',
        'totale_merce' => 'integer',
        'spedizione' => 'integer',
        'totale' => 'integer',
        'crediti_usati' => 'integer',
        'richiede_lavorazione' => 'boolean',
        'foto_bloccata' => 'boolean',
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

    /** I servizi editor attivati su questo ordine (ricordini/manifesti/necrologi), se è un ordine di solo servizio. */
    public function servizi(): HasMany
    {
        return $this->hasMany(OrdineServizio::class);
    }

    /**
     * Se aprire il designer `$codice` (ricordini/manifesti/necrologi) è
     * consentito su questo ordine.
     *
     * Un ordine senza righe in `ordine_servizi` è un ordine "normale" (un
     * kit fisico comprato dal Catalog): l'uso dei designer è incluso, come
     * deciso dal committente ("col kit trigesimo il designer è incluso") —
     * accesso pieno, nessun gating. Un ordine DI SOLO SERVIZIO invece apre
     * solo i designer corrispondenti ai servizi effettivamente attivati.
     */
    public function designerAbilitato(string $codice): bool
    {
        if ($this->servizi->isEmpty()) {
            return true;
        }

        return $this->servizi->contains(fn (OrdineServizio $s) => $s->servizioEditor?->codice === $codice);
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
     * Preferisce `occasione`, scelta esplicitamente dall'agenzia (etichetta
     * indipendente da quali servizi sono stati attivati): un Funerale
     * suggerisce il Trigesimo fra 30 giorni, un Trigesimo suggerisce
     * l'Anniversario fra un anno, un
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

    /**
     * Quanto di questo ordine è in denaro, dopo aver scalato i crediti
     * già applicati (vedi CreaOrdine, cambio fisso 1 credito = 100
     * centesimi): la base sia per l'incasso (quanto addebita Stripe, vedi
     * PagamentoStripe) sia per la contabilità (vedi
     * GestioneAgenzieController::movimenti) — prima e dopo il saldo è la
     * stessa cifra, cambia solo se leggerla come "da incassare" o
     * "incassato".
     */
    public function valoreInDenaro(): int
    {
        return max(0, $this->totale - ($this->crediti_usati * 100));
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

    /**
     * Una volta confermata la foto principale della pratica, il Foto Manager
     * si blocca (sola visualizzazione): vedi WizardApiController.
     */
    public function fotoBloccata(): bool
    {
        return (bool) $this->foto_bloccata;
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

    /**
     * Una fattura per ordine: lo staff la emette (fuori da qui, nel proprio
     * gestionale) e registra qui solo il numero, per la contabilità che
     * l'agenzia vede in `account/fatture`. Non tocca `stato_pagamento`: un
     * ordine può essere fatturato e restare da saldare — il saldo si
     * registra a parte con `registraPagamento()`, riusato anche qui perché
     * "un ordine a fattura pagato" non è diverso concettualmente da uno
     * pagato a carta, solo il riferimento cambia (un bonifico invece di un
     * PaymentIntent Stripe).
     */
    public function emettiFattura(string $numero): void
    {
        $this->forceFill([
            'fattura_numero' => $numero,
            'fattura_emessa_at' => Carbon::now(),
        ])->save();
    }

    public function fatturata(): bool
    {
        return $this->fattura_emessa_at !== null;
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
