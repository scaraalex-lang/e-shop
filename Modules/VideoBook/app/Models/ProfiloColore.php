<?php

namespace Modules\VideoBook\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Un caricamento del profilo ICC del laboratorio di stampa (vedi la
 * migration): il file vive sul disco `local`, mai `public` — non serve un
 * URL raggiungibile da fuori, solo dal server.
 *
 * Ogni caricamento è una riga nuova: attivo() prende sempre l'ultima, la
 * storia di quali profili si sono succeduti resta per intero invece di
 * sovrascrivere una riga singola.
 */
class ProfiloColore extends Model
{
    protected $table = 'videobook_profili_colore';

    protected $fillable = ['path', 'nome_originale', 'caricato_da'];

    public function caricatoDa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'caricato_da');
    }

    /** Il profilo in uso oggi, o null se nessuno è mai stato caricato. */
    public static function attivo(): ?self
    {
        return static::latest()->first();
    }

    /** Percorso assoluto sul disco, per chi (in una fase successiva) dovrà davvero leggere il profilo — non un URL. */
    public function percorsoAssoluto(): string
    {
        return Storage::disk('local')->path($this->path);
    }
}
