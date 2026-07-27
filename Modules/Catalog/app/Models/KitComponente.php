<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Una riga della composizione di un kit: un articolo e quanti pezzi ne contiene. */
class KitComponente extends Model
{
    protected $table = 'kit_componenti';

    protected $fillable = ['kit_product_id', 'componente_product_id', 'quantita'];

    protected $casts = [
        'quantita' => 'integer',
    ];

    public function kit(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'kit_product_id');
    }

    public function componente(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'componente_product_id');
    }
}
