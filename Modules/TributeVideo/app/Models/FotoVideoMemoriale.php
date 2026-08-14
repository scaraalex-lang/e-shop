<?php

namespace Modules\TributeVideo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una foto nella sequenza Ken Burns di un video memoriale.
 */
class FotoVideoMemoriale extends Model
{
    protected $table = 'foto_video_memoriale';

    protected $fillable = ['video_memoriale_id', 'path', 'ordine'];

    public function videoMemoriale(): BelongsTo
    {
        return $this->belongsTo(VideoMemoriale::class);
    }

    /** Path relativo sul disco public: mai un URL assoluto. */
    public function url(): string
    {
        return '/storage/'.ltrim($this->path, '/');
    }
}
