<?php

namespace Modules\VideoBook\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Modules\VideoBook\Enums\StatoRenderVideo;

/**
 * Il video renderizzato di un libro — l'"export" di cui parla il commento
 * su [[StatoLibro]]: qui vive lo stato del render, non sul Libro.
 *
 * Stesso schema di [[\Modules\TributeVideo\Models\VideoMemoriale]]: coda ->
 * elaborazione -> pronto/errore, stesso proxy Python dietro le quinte
 * (nuovo endpoint widescreen, stesso motore ffmpeg Ken Burns).
 */
class Video extends Model
{
    protected $table = 'videobook_video';

    protected $fillable = [
        'libro_id', 'stato', 'render_avviato_il',
        'cloudinary_url', 'cloudinary_public_id', 'messaggio_errore',
    ];

    protected $casts = [
        'stato' => StatoRenderVideo::class,
        'render_avviato_il' => 'datetime',
    ];

    public function libro(): BelongsTo
    {
        return $this->belongsTo(Libro::class, 'libro_id');
    }

    public function inCorso(): bool
    {
        return in_array($this->stato, [StatoRenderVideo::InCoda, StatoRenderVideo::InElaborazione], true);
    }

    public function pronto(): bool
    {
        return $this->stato === StatoRenderVideo::Pronto;
    }

    public function inCoda(): void
    {
        $this->forceFill(['stato' => StatoRenderVideo::InCoda, 'render_avviato_il' => now(), 'messaggio_errore' => null])->save();
    }

    public function inElaborazione(): void
    {
        $this->forceFill(['stato' => StatoRenderVideo::InElaborazione])->save();
    }

    public function segnaPronto(string $cloudinaryUrl, string $cloudinaryPublicId): void
    {
        $this->forceFill([
            'stato' => StatoRenderVideo::Pronto,
            'cloudinary_url' => $cloudinaryUrl,
            'cloudinary_public_id' => $cloudinaryPublicId,
            'messaggio_errore' => null,
        ])->save();
    }

    public function segnaErrore(string $messaggio): void
    {
        $this->forceFill(['stato' => StatoRenderVideo::Errore, 'messaggio_errore' => $messaggio])->save();
    }

    /** Secondi trascorsi dall'avvio di questo render — per la UI di attesa. */
    public function secondiTrascorsi(): int
    {
        return (int) ($this->render_avviato_il ?? $this->created_at)->diffInSeconds(now());
    }

    /**
     * Nome del file scaricato: servizio + codice del libro — stesso
     * ragionamento di VideoMemoriale::nomeFile()/Reel::nomeFile(), niente
     * public_id casuale in cartella Download.
     */
    public function nomeFile(): string
    {
        return 'videobook-'.Str::padLeft((string) $this->libro_id, 6, '0');
    }

    /** Stesso motivo di VideoMemoriale::downloadUrl(): fl_attachment, non l'attributo HTML download. */
    public function downloadUrl(): ?string
    {
        if (! $this->cloudinary_url) {
            return null;
        }

        return str_replace('/upload/', '/upload/fl_attachment:'.$this->nomeFile().'/', $this->cloudinary_url);
    }
}
