<?php

namespace Modules\Commerce\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Modules\Commerce\Enums\StatoAgenzia;

class Agenzia extends Model
{
    use SoftDeletes;

    protected $table = 'agenzie';

    protected $fillable = [
        'ragione_sociale', 'partita_iva', 'codice_fiscale',
        'codice_sdi', 'pec',
        'indirizzo', 'cap', 'citta', 'provincia', 'nazione', 'telefono',
        'ordine_minimo_pezzi',
    ];

    /**
     * Stato e note interne non sono mai assegnabili in massa: cambiano solo
     * dai metodi qui sotto, che tengono traccia di chi e quando.
     */
    protected $casts = [
        'stato' => StatoAgenzia::class,
        'stato_aggiornato_at' => 'datetime',
        'ordine_minimo_pezzi' => 'integer',
    ];

    protected $attributes = [
        'stato' => 'in_attesa',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
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
