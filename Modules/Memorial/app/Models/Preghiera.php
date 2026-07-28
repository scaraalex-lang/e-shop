<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Testo di preghiera dell'archivio, scegliibile dalla galleria del
 * Designer Smart. Non contiene dati personali: è un testo riusabile.
 */
class Preghiera extends Model
{
    protected $table = 'preghiere';

    protected $fillable = ['titolo', 'testo', 'categoria', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeAttive(Builder $q): Builder
    {
        return $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /** Prime righe, per l'anteprima nella galleria. */
    public function estratto(int $righe = 2): string
    {
        return collect(preg_split('/\R/', trim($this->testo)))
            ->take($righe)
            ->implode(' ');
    }
}
