<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Un servizio editor attivabile a crediti (ricordini/manifesti/necrologi).
 * Righe fisse, gestite solo da /gestione/servizi: costo e attivo/non
 * attivo, niente create/delete da UI.
 */
class ServizioEditor extends Model
{
    protected $table = 'servizi_editor';

    protected $fillable = ['codice', 'etichetta', 'costo_crediti', 'attivo'];

    protected $casts = [
        'costo_crediti' => 'integer',
        'attivo' => 'boolean',
    ];

    public function attivazioni(): HasMany
    {
        return $this->hasMany(OrdineServizio::class);
    }

    public function scopeAttivi(Builder $query): Builder
    {
        return $query->where('attivo', true);
    }
}
