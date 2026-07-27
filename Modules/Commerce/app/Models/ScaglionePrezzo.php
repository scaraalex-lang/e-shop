<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Prezzi\Concerns\FormattaSconto;
use Modules\Commerce\Prezzi\Contracts\FonteSconto;

/**
 * Uno scaglione di sconto quantità su un prodotto: da `quantita_minima` pezzi
 * in su, l'agenzia approvata paga `sconto_percentuale` in meno del pubblico.
 */
class ScaglionePrezzo extends Model implements FonteSconto
{
    use FormattaSconto;

    protected $table = 'scaglioni_prezzo';

    protected $fillable = ['product_id', 'quantita_minima', 'sconto_percentuale'];

    protected $casts = [
        'quantita_minima' => 'integer',
        'sconto_percentuale' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
