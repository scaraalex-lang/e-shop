<?php

namespace Modules\Commerce\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Commerce\Prezzi\Conto;
use Modules\Commerce\Prezzi\Listino;
use Modules\Commerce\Prezzi\VoceConto;

class Carrello extends Model
{
    protected $table = 'carrelli';

    protected $fillable = ['user_id', 'token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function righe(): HasMany
    {
        return $this->hasMany(RigaCarrello::class)->orderBy('id');
    }

    public function vuoto(): bool
    {
        return $this->righe->isEmpty();
    }

    /** Quanti pezzi in tutto: è l'unità del minimo d'ordine B2B. */
    public function pezzi(): int
    {
        return (int) $this->righe->sum(fn (RigaCarrello $r) => $r->pezzi());
    }

    /**
     * Solo pacchetti crediti, nessun pezzo fisico: il minimo d'ordine B2B non
     * si applica, non c'è niente da spedire in blocco (vedi CheckoutController).
     */
    public function soloCrediti(): bool
    {
        return $this->righe->isNotEmpty()
            && $this->righe->every(fn (RigaCarrello $r) => (bool) $r->product?->crediti);
    }

    /**
     * Il conto del carrello con il listino di questo account: righe, costi e
     * totali, calcolati una volta sola e con una sola query sugli scaglioni.
     */
    public function conto(Listino $listino, ?User $utente): Conto
    {
        $this->loadMissing('righe.product');
        $listino->precarica($this->righe->pluck('product')->filter());

        return new Conto(
            $this->righe->map(fn (RigaCarrello $riga) => new VoceConto(
                $riga,
                $listino->perRiga($riga->product, $riga->quantita, $utente),
            ))
        );
    }
}
