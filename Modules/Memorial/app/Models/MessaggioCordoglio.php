<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Un messaggio di cordoglio lasciato da chi visita la pagina del manifesto, senza account. */
class MessaggioCordoglio extends Model
{
    protected $table = 'messaggi_cordoglio';

    protected $fillable = ['necrologio_id', 'nome', 'messaggio'];

    public function necrologio(): BelongsTo
    {
        return $this->belongsTo(Necrologio::class);
    }
}
