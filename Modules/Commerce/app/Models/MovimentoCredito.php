<?php

namespace Modules\Commerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Un movimento sul saldo crediti di un'agenzia.
 *
 * Positivo quando si accredita (l'acquisto di un pacchetto), negativo quando
 * si spende — il consumo non è ancora costruito, ma il segno è già pensato
 * per quello. Il saldo non vive qui: è la somma della storia, vedi
 * [[Agenzia::creditiSaldo]].
 */
class MovimentoCredito extends Model
{
    protected $table = 'movimenti_credito';

    protected $fillable = ['agenzia_id', 'ordine_id', 'quantita', 'causale'];

    protected $casts = [
        'quantita' => 'integer',
    ];

    public function agenzia(): BelongsTo
    {
        return $this->belongsTo(Agenzia::class);
    }

    public function ordine(): BelongsTo
    {
        return $this->belongsTo(Ordine::class);
    }
}
