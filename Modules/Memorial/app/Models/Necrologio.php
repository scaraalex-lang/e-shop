<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\Commerce\Enums\Occasione;

class Necrologio extends Model
{
    protected $table = 'necrologi';

    /** Oltre questa durata scelta dall'agenzia, l'acquisto embed diventa "perpetuo" (vedi abilitaEmbed). */
    public const EMBED_TERMINE_MESI_MASSIMI = 6;

    /** "Perpetuo" è garantito solo fino a questo orizzonte, non davvero infinito. */
    public const EMBED_PERPETUO_ANNI = 3;

    protected $fillable = [
        'defunto_id', 'agenzia_id', 'percorso', 'occasione', 'numero_anniversario',
        'trigesimo_at', 'trigesimo_luogo', 'trigesimo_indirizzo', 'testo',
        'og_image', 'card_canvas', 'manifesto', 'manifesto_anteprima', 'manifesto_canvas', 'manifesto_formato',
    ];

    protected $casts = [
        'occasione' => Occasione::class,
        'numero_anniversario' => 'integer',
        'trigesimo_at' => 'datetime',
        'pubblicazione_consenso' => 'boolean',
        'pubblicazione_autorizzata_at' => 'datetime',
        'pubblicato' => 'boolean',
        'pubblicato_fino_al' => 'date',
        'embed_abilitato' => 'boolean',
        'embed_scaduto_il' => 'date',
        'card_canvas' => 'array',
        'manifesto_canvas' => 'array',
    ];

    public function defunto(): BelongsTo
    {
        return $this->belongsTo(Defunto::class);
    }

    public function messaggiCordoglio(): HasMany
    {
        return $this->hasMany(MessaggioCordoglio::class)->latest();
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

    public function urlManifesto(string $slugAgenzia): string
    {
        return route('necrologio.manifesto.pubblico', ['agenzia' => $slugAgenzia, 'percorso' => $this->percorso]);
    }

    /** Path relativo (mai `Storage::url()`/`url()`, vedi CLAUDE.md): evita l'host sbagliato di APP_URL=localhost. */
    public function manifestoAnteprimaUrl(): ?string
    {
        return $this->manifesto_anteprima ? '/storage/'.ltrim($this->manifesto_anteprima, '/') : null;
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

    /**
     * L'URL pubblico (social/WhatsApp) è sempre incluso nel servizio
     * necrologio. Incorporarlo in un iframe sul sito dell'agenzia è un
     * livello a pagamento a parte: serve sia il consenso/interruttore che
     * l'acquisto — vedi NecrologiController::acquistaEmbed().
     */
    public function embeddabile(): bool
    {
        return $this->embed_abilitato && $this->pubblico() && ! $this->embedScaduto();
    }

    /**
     * Vero solo per un embed "a termine" oltre la sua scadenza. Un embed
     * "perpetuo" (o comprato prima di questa modifica, `embed_scaduto_il`
     * null) non scade mai da qui: vedi EMBED_PERPETUO_ANNI per il limite
     * tecnico impostato all'acquisto.
     */
    public function embedScaduto(): bool
    {
        return $this->embed_scaduto_il !== null
            && $this->embed_scaduto_il->endOfDay()->isPast();
    }

    /**
     * Accende l'embed (o lo rinnova, se era "a termine" e scaduto). Non ha un
     * "ritira" come il consenso alla pubblicazione: resta acceso fino alla
     * scadenza scelta.
     */
    public function abilitaEmbed(Carbon $scadenza, string $tipo): void
    {
        $this->forceFill([
            'embed_abilitato' => true,
            'embed_scaduto_il' => $scadenza,
            'embed_tipo' => $tipo,
        ])->save();
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
