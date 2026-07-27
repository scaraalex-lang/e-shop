<?php

namespace Modules\Commerce\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Modules\Commerce\Enums\StatoAgenzia;

class Agenzia extends Model
{
    use SoftDeletes;

    protected $table = 'agenzie';

    protected $fillable = [
        'ragione_sociale', 'slug', 'partita_iva', 'codice_fiscale',
        'codice_sdi', 'pec',
        'indirizzo', 'cap', 'citta', 'provincia', 'nazione', 'telefono',
        'ordine_minimo_pezzi',
    ];

    /**
     * Stato, note interne, agente e sconto personale non sono mai
     * assegnabili in massa: cambiano solo dai metodi qui sotto, che tengono
     * traccia di chi e quando.
     */
    protected $casts = [
        'stato' => StatoAgenzia::class,
        'stato_aggiornato_at' => 'datetime',
        'ordine_minimo_pezzi' => 'integer',
        'sconto_percentuale' => 'decimal:2',
    ];

    protected $attributes = [
        'stato' => 'in_attesa',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function movimentiCredito(): HasMany
    {
        return $this->hasMany(MovimentoCredito::class);
    }

    public function agenteVendita(): BelongsTo
    {
        return $this->belongsTo(AgenteVendita::class);
    }

    /** Chi segue quest'agenzia. Null per toglierlo senza assegnarne un altro. */
    public function assegnaAgente(?AgenteVendita $agente): void
    {
        $this->forceFill(['agente_vendita_id' => $agente?->id])->save();
    }

    /**
     * La percentuale personale di questa agenzia: sostituisce gli scaglioni
     * generici sul prodotto (vedi Listino::scontoApplicabile), non si somma.
     * Null per tornare agli scaglioni standard.
     */
    public function impostaScontoPersonale(?float $percentuale): void
    {
        $this->forceFill(['sconto_percentuale' => $percentuale])->save();
    }

    /**
     * Il saldo crediti: somma della storia dei movimenti, mai un numero a
     * parte da tenere sincronizzato a mano (vedi [[MovimentoCredito]]).
     */
    public function creditiSaldo(): int
    {
        return (int) $this->movimentiCredito()->sum('quantita');
    }

    /**
     * L'indirizzo a cui scrive chi risponde a un'email partita per conto di
     * questa agenzia.
     *
     * È l'email dell'account: un'agenzia ha un solo login, quindi è la casella
     * che legge davvero. Le email partono dal nostro SMTP — mettere l'agenzia
     * nel mittente farebbe fallire SPF sul suo dominio — ma la risposta deve
     * arrivare a lei, non a noi: è lei che sta seguendo quella famiglia.
     */
    public function emailContatto(): ?string
    {
        return $this->user?->email;
    }

    /**
     * Lo spicchio di indirizzo pubblico: /ricordi/{slug}/...
     * Si genera alla creazione se non è stato deciso a mano.
     */
    protected static function booted(): void
    {
        static::creating(function (self $agenzia) {
            if (blank($agenzia->slug)) {
                $agenzia->slug = static::slugLibero($agenzia->ragione_sociale, $agenzia->citta);
            }
        });
    }

    public static function slugLibero(string $ragioneSociale, ?string $citta = null): string
    {
        $base = Str::slug($ragioneSociale) ?: 'agenzia';

        if (! static::where('slug', $base)->exists()) {
            return $base;
        }

        $conCitta = Str::slug($base.'-'.$citta);

        if ($citta && ! static::where('slug', $conCitta)->exists()) {
            return $conCitta;
        }

        return $base.'-'.Str::lower(Str::random(4));
    }

    public function scopeInAttesa(Builder $query): Builder
    {
        return $query->where('stato', StatoAgenzia::InAttesa);
    }

    public function scopeApprovate(Builder $query): Builder
    {
        return $query->where('stato', StatoAgenzia::Approvata);
    }

    public function eApprovata(): bool
    {
        return $this->stato->abilitaCondizioniB2b();
    }

    public function approva(User $staff, ?string $note = null): void
    {
        $this->aggiornaStato(StatoAgenzia::Approvata, $staff, noteInterne: $note);
    }

    public function rifiuta(User $staff, string $motivo, ?string $note = null): void
    {
        $this->aggiornaStato(StatoAgenzia::Rifiutata, $staff, $motivo, $note);
    }

    public function sospendi(User $staff, ?string $note = null): void
    {
        $this->aggiornaStato(StatoAgenzia::Sospesa, $staff, noteInterne: $note);
    }

    private function aggiornaStato(
        StatoAgenzia $stato,
        User $staff,
        ?string $motivo = null,
        ?string $noteInterne = null,
    ): void {
        $this->forceFill([
            'stato' => $stato,
            'stato_aggiornato_at' => Carbon::now(),
            'stato_aggiornato_da' => $staff->id,
            'motivo_rifiuto' => $stato === StatoAgenzia::Rifiutata ? $motivo : null,
            'note_interne' => $noteInterne ?? $this->note_interne,
        ])->save();
    }

    /**
     * Indirizzo di consegna B2B: la merce va all'agenzia, che poi consegna
     * alla famiglia.
     */
    public function indirizzoCompleto(): string
    {
        return "{$this->indirizzo}, {$this->cap} {$this->citta} ({$this->provincia})";
    }

    /**
     * Minimo d'ordine in NUMERO DI PEZZI: quello concordato con questa agenzia,
     * altrimenti il valore generale di configurazione.
     */
    public function ordineMinimoPezzi(): int
    {
        return $this->ordine_minimo_pezzi ?? (int) config('commerce.ordine_minimo_pezzi', 0);
    }
}
