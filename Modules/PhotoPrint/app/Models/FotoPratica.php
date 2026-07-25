<?php

namespace Modules\PhotoPrint\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Una fotografia della lavorazione, legata all'ordine che l'ha richiesta.
 */
class FotoPratica extends Model
{
    protected $table = 'foto_pratica';

    protected $fillable = ['ordine_id', 'path', 'tipo', 'is_principale'];

    protected $casts = [
        'ordine_id' => 'integer',
        'is_principale' => 'boolean',
    ];

    /** Path relativo, mai un URL assoluto: vedi la migration. */
    public function url(): string
    {
        return '/storage/'.ltrim($this->path, '/');
    }

    /**
     * Rende questa la foto principale della lavorazione, togliendo il segno
     * alle altre dello stesso ordine.
     */
    public function rendiPrincipale(): void
    {
        static::where('ordine_id', $this->ordine_id)
            ->whereKeyNot($this->id)
            ->update(['is_principale' => false]);

        $this->forceFill(['is_principale' => true])->save();
    }

    /** La forma che si aspetta la galleria del Foto Manager. */
    public function perGalleria(): array
    {
        return [
            'id' => $this->id,
            'url' => $this->url(),
            'is_principale' => $this->is_principale,
            'tipo' => $this->tipo,
        ];
    }
}
