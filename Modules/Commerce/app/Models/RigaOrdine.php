<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

class RigaOrdine extends Model
{
    protected $table = 'righe_ordine';

    protected $fillable = [
        'product_id', 'sku', 'nome', 'quantita',
        'prezzo_pieno', 'prezzo', 'sconto_percentuale', 'richiede_foto',
    ];

    protected $casts = [
        'quantita' => 'integer',
        'prezzo_pieno' => 'integer',
        'prezzo' => 'integer',
        'sconto_percentuale' => 'decimal:2',
        'richiede_foto' => 'boolean',
    ];

    public function ordine(): BelongsTo
    {
        return $this->belongsTo(Ordine::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function haSconto(): bool
    {
        return $this->prezzo < $this->prezzo_pieno;
    }
}
