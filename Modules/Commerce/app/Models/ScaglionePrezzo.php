<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

/**
 * Uno scaglione di sconto quantità su un prodotto: da `quantita_minima` pezzi
 * in su, l'agenzia approvata paga `sconto_percentuale` in meno del pubblico.
 */
class ScaglionePrezzo extends Model
{
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

    /**
     * Lo sconto in centesimi di punto percentuale (12,50% → 1250).
     *
     * Serve a fare i conti in aritmetica intera: il denaro non passa mai da un
     * float, nemmeno di sfroso attraverso la percentuale.
     */
    public function scontoInCentesimiDiPunto(): int
    {
        return (int) round(((float) $this->sconto_percentuale) * 100);
    }

    /** Etichetta pronta per la vetrina: "12,5%" senza zeri inutili. */
    public function scontoLeggibile(): string
    {
        return rtrim(rtrim(number_format((float) $this->sconto_percentuale, 2, ',', '.'), '0'), ',').'%';
    }
}
