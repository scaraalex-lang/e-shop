<?php

namespace Modules\Memorial\Models;

use Illuminate\Database\Eloquent\Model;

class Santo extends Model
{
    protected $table = 'santi';

    protected $fillable = ['nome', 'path'];

    /**
     * URL relativo dell'immagine (es. /storage/...): il browser lo risolve
     * sull'origine corrente. Evita il problema di APP_URL=http://localhost, che
     * renderebbe l'immagine irraggiungibile dal browser dell'utente.
     */
    public function url(): string
    {
        return '/storage/' . ltrim($this->path, '/');
    }
}
