<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

/**
 * Una riga del carrello. Nessun prezzo salvato: si ricalcola sempre col
 * listino del momento — vedi la migration di `righe_carrello`.
 */
class RigaCarrello extends Model
{
    protected $table = 'righe_carrello';

    protected $fillable = ['carrello_id', 'product_id', 'quantita', 'numero_anniversario', 'defunto_id'];

    protected $casts = ['quantita' => 'integer', 'numero_anniversario' => 'integer', 'defunto_id' => 'integer'];

    public function carrello(): BelongsTo
    {
        return $this->belongsTo(Carrello::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Quanti pezzi conta questa riga per il minimo d'ordine B2B.
     *
     * Per un kit la quantità È già il numero di ricordini, quindi vale così
     * com'è: non si moltiplica per i pezzi inclusi. Un pacchetto crediti non
     * è un pezzo da spedire: non deve contribuire al minimo, altrimenti
     * un'agenzia non potrebbe mai comprare solo crediti senza un kit.
     */
    public function pezzi(): int
    {
        return $this->product?->crediti ? 0 : $this->quantita;
    }
}
