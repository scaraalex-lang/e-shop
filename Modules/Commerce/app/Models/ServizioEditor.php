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

    protected $fillable = ['codice', 'etichetta', 'costo_crediti', 'costo_crediti_a_termine', 'attivo'];

    protected $casts = [
        'costo_crediti' => 'integer',
        'costo_crediti_a_termine' => 'integer',
        'attivo' => 'boolean',
    ];

    /**
     * I soli codici attivabili su un ordine nuovo (checkbox in "Acquisto
     * servizio digitale"): sbloccano un designer via Ordine::designerAbilitato().
     * `embed` sta nella stessa tabella (riusa il costo in crediti modificabile
     * da staff) ma si acquista direttamente da un necrologio già esistente,
     * non da un ordine — vedi NecrologiController::acquistaEmbed().
     */
    public const CODICI_ORDINE = ['ricordini', 'manifesti', 'necrologi'];

    public function attivazioni(): HasMany
    {
        return $this->hasMany(OrdineServizio::class);
    }

    public function scopeAttivi(Builder $query): Builder
    {
        return $query->where('attivo', true);
    }

    public function scopeAttivabiliSuOrdine(Builder $query): Builder
    {
        return $query->whereIn('codice', self::CODICI_ORDINE);
    }
}
