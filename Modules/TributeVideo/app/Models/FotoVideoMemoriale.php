<?php

namespace Modules\TributeVideo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\TributeVideo\Enums\PosizioneDidascalia;

/**
 * Una foto nella sequenza Ken Burns di un video memoriale.
 */
class FotoVideoMemoriale extends Model
{
    protected $table = 'foto_video_memoriale';

    protected $fillable = [
        'video_memoriale_id', 'path', 'ordine',
        'testo', 'testo_posizione', 'durata_secondi', 'zoom_attivo',
    ];

    protected $casts = [
        'testo_posizione' => PosizioneDidascalia::class,
        'zoom_attivo' => 'boolean',
    ];

    public function videoMemoriale(): BelongsTo
    {
        return $this->belongsTo(VideoMemoriale::class);
    }

    /** Path relativo sul disco public: mai un URL assoluto. */
    public function url(): string
    {
        return '/storage/'.ltrim($this->path, '/');
    }

    public function haDidascalia(): bool
    {
        return filled($this->testo);
    }
}
