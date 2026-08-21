<?php

namespace Modules\TributeVideo\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Modules\TributeVideo\Enums\StatoVideoMemoriale;

/**
 * Un video memoriale in lavorazione o pronto.
 *
 * `defunto_id`/`ordine_id` sono null nell'MVP di test staff: il video nasce
 * da testo libero, senza toccare Memorial/Commerce (vedi migration).
 */
class VideoMemoriale extends Model
{
    protected $table = 'video_memoriali';

    protected $fillable = [
        'token', 'defunto_id', 'ordine_id', 'stato', 'render_avviato_il',
        'nome_completo', 'date', 'citazione', 'audio_path',
        'cloudinary_url', 'cloudinary_public_id', 'output_path',
        'messaggio_errore',
    ];

    protected $casts = [
        'stato' => StatoVideoMemoriale::class,
        'render_avviato_il' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    public function foto(): HasMany
    {
        return $this->hasMany(FotoVideoMemoriale::class)->orderBy('ordine');
    }

    /** Il link pubblico è l'unica credenziale: dev'essere lungo e casuale. */
    public static function nuovoToken(): string
    {
        return Str::random(64);
    }

    public function pronto(): bool
    {
        return $this->stato === StatoVideoMemoriale::Pronto;
    }

    /**
     * Si può riaprire l'editor solo a render concluso (bene o male): mentre è
     * in coda o in elaborazione il Job sta già leggendo `foto()`, editare in
     * quel momento significherebbe modificare righe che il render sta usando.
     */
    public function modificabile(): bool
    {
        return in_array($this->stato, [StatoVideoMemoriale::Pronto, StatoVideoMemoriale::Errore], true);
    }

    /** Path relativo sul disco public, mai un URL assoluto — stesso schema di FotoVideoMemoriale::url(). */
    public function audioUrl(): ?string
    {
        return $this->audio_path ? '/storage/'.ltrim($this->audio_path, '/') : null;
    }

    /**
     * Fotogramma di anteprima estratto automaticamente da Cloudinary
     * (stesso public_id, formato immagine invece di video) — usato come
     * `poster` del player e come `og:image` per le anteprime di condivisione.
     */
    public function posterUrl(): ?string
    {
        if (! $this->cloudinary_url) {
            return null;
        }

        return preg_replace('/\.mp4$/', '.jpg', $this->cloudinary_url);
    }

    public function inCoda(): void
    {
        $this->forceFill(['stato' => StatoVideoMemoriale::InCoda])->save();
    }

    public function inElaborazione(): void
    {
        $this->forceFill(['stato' => StatoVideoMemoriale::InElaborazione])->save();
    }

    public function segnaPronto(string $cloudinaryUrl, string $cloudinaryPublicId, ?string $outputPath): void
    {
        $this->forceFill([
            'stato' => StatoVideoMemoriale::Pronto,
            'cloudinary_url' => $cloudinaryUrl,
            'cloudinary_public_id' => $cloudinaryPublicId,
            'output_path' => $outputPath,
            'messaggio_errore' => null,
        ])->save();
    }

    public function segnaErrore(string $messaggio): void
    {
        $this->forceFill([
            'stato' => StatoVideoMemoriale::Errore,
            'messaggio_errore' => $messaggio,
        ])->save();
    }
}
