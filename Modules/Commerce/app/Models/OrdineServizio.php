<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un servizio attivato su un ordine, col costo congelato al momento
 * dell'attivazione (vedi RigaOrdine per lo stesso principio sui prodotti).
 */
class OrdineServizio extends Model
{
    protected $table = 'ordine_servizi';

    protected $fillable = ['ordine_id', 'servizio_editor_id', 'costo_crediti'];

    protected $casts = ['costo_crediti' => 'integer'];

    public function ordine(): BelongsTo
    {
        return $this->belongsTo(Ordine::class);
    }

    public function servizioEditor(): BelongsTo
    {
        return $this->belongsTo(ServizioEditor::class);
    }
}
