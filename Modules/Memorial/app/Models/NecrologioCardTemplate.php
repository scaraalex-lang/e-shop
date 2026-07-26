<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Sfondo PNG riusabile del card designer, caricato da un'agenzia.
 *
 * Scoping per `agenzia_id`: ogni agenzia vede e gestisce solo i propri, come
 * per i necrologi stessi — non è informazione da condividere fra concorrenti.
 */
class NecrologioCardTemplate extends Model
{
    protected $table = 'necrologio_card_templates';

    protected $fillable = ['agenzia_id', 'nome', 'path'];

    /**
     * URL relativo (stessa scelta di [[Santo::url]]): evita il problema di
     * APP_URL=http://localhost, che renderebbe l'immagine irraggiungibile.
     */
    public function url(): string
    {
        return '/storage/'.ltrim($this->path, '/');
    }
}
