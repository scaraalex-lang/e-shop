<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Necrologio extends Model
{
    protected $table = 'necrologi';

    protected $fillable = [
        'defunto_id', 'agenzia_id', 'percorso',
        'trigesimo_at', 'trigesimo_luogo', 'trigesimo_indirizzo', 'testo',
        'og_image', 'manifesto',
    ];

    protected $casts = [
        'trigesimo_at' => 'datetime',
        'pubblicazione_consenso' => 'boolean',
        'pubblicazione_autorizzata_at' => 'datetime',
        'pubblicato' => 'boolean',
        'pubblicato_fino_al' => 'date',
    ];

    public function defunto(): BelongsTo
    {
        return $this->belongsTo(Defunto::class);
    }

    /**
     * L'indirizzo pubblico: "luigia-rossetti-a7f3".
     *
     * Il codice in coda non è decorazione. Senza, chiunque potrebbe tirare
     * fuori l'elenco dei morti di un'agenzia cambiando il nome nell'indirizzo;
     * e un indirizzo ritirato diventerebbe la chiave per indovinare gli altri.
     */
    public static function componiPercorso(Defunto $defunto): string
    {
        $nome = Str::slug($defunto->nomeCompleto()) ?: 'ricordo';

        return $nome.'-'.Str::lower(Str::random(4));
    }

    public function url(string $slugAgenzia): string
    {
        return route('necrologio', ['agenzia' => $slugAgenzia, 'percorso' => $this->percorso]);
    }

    /**
     * È visibile a un estraneo?
     *
     * Tre condizioni insieme, e nessuna basta da sola: il familiare ha
     * autorizzato la pubblicazione, l'agenzia ha acceso l'interruttore, e la
     * data di spegnimento non è passata.
     */
    public function pubblico(): bool
    {
        return $this->pubblicazione_consenso
            && $this->pubblicato
            && ! $this->scaduto();
    }

    public function scaduto(): bool
    {
        return $this->pubblicato_fino_al !== null
            && $this->pubblicato_fino_al->endOfDay()->isPast();
    }

    public function scopePubblici(Builder $query): Builder
    {
        return $query
            ->where('pubblicazione_consenso', true)
            ->where('pubblicato', true)
            ->where(fn ($q) => $q->whereNull('pubblicato_fino_al')->orWhereDate('pubblicato_fino_al', '>=', today()));
    }

    /**
     * Registra il consenso alla pubblicazione: chi autorizza, in che
     * parentela, quando. Non si eredita da quello sul defunto.
     */
    public function autorizzaPubblicazione(string $da, string $parentela): void
    {
        $this->forceFill([
            'pubblicazione_consenso' => true,
            'pubblicazione_autorizzata_da' => $da,
            'pubblicazione_parentela' => $parentela,
            'pubblicazione_autorizzata_at' => Carbon::now(),
        ])->save();
    }

    public function revocaConsenso(): void
    {
        $this->forceFill([
            'pubblicazione_consenso' => false,
            'pubblicato' => false,
        ])->save();
    }

    /**
     * Accende la pubblicazione. Senza consenso non si accende: è la
     * condizione, non un passaggio da saltare.
     */
    public function pubblica(?Carbon $finoAl = null): bool
    {
        if (! $this->pubblicazione_consenso) {
            return false;
        }

        $this->forceFill([
            'pubblicato' => true,
            'pubblicato_fino_al' => $finoAl ?? $this->scadenzaPredefinita(),
        ])->save();

        return true;
    }

    public function ritira(): void
    {
        $this->forceFill(['pubblicato' => false])->save();
    }

    /**
     * Quindici giorni dopo il trigesimo: la card serve nei giorni intorno
     * all'evento, non per sempre. Meno esposizione, meno richieste di
     * rimozione, meno dati di defunti custoditi senza motivo.
     */
    public function scadenzaPredefinita(): Carbon
    {
        return ($this->trigesimo_at?->copy() ?? Carbon::now()->addDays(30))->addDays(15)->startOfDay();
    }

    /** Quanto manca al trigesimo, per il promemoria all'agenzia. */
    public function giorniAlTrigesimo(): ?int
    {
        return $this->trigesimo_at
            ? (int) Carbon::now()->startOfDay()->diffInDays($this->trigesimo_at->copy()->startOfDay(), false)
            : null;
    }
}
