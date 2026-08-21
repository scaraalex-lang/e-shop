<?php

namespace Modules\ReelSocial\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\ReelSocial\Enums\StatoReel;

/**
 * Un reel: copertina della Storia Social + Video Memoriale concatenati in un
 * unico mp4 verticale, condiviso via link pubblico permanente — stesso
 * schema di Modules\TributeVideo\Models\VideoMemoriale.
 */
class Reel extends Model
{
    protected $table = 'reels';

    protected $fillable = [
        'token', 'defunto_id', 'storia_social_id', 'video_memoriale_id',
        'stato', 'render_avviato_il',
        'cloudinary_url', 'cloudinary_public_id', 'output_path', 'messaggio_errore',
    ];

    protected $casts = [
        'stato' => StatoReel::class,
        'render_avviato_il' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'token';
    }

    /** Il link pubblico è l'unica credenziale: dev'essere lungo e casuale. */
    public static function nuovoToken(): string
    {
        return Str::random(64);
    }

    public function pronto(): bool
    {
        return $this->stato === StatoReel::Pronto;
    }

    /**
     * URL di download diretto: l'attributo HTML `download` da solo non
     * basta per un file cross-origin come Cloudinary (i browser lo
     * ignorano fuori dal proprio dominio) — serve il flag `fl_attachment`
     * di Cloudinary, che fa rispondere con `Content-Disposition: attachment`
     * indipendentemente dall'origine.
     */
    public function downloadUrl(): ?string
    {
        if (! $this->cloudinary_url) {
            return null;
        }

        return str_replace('/upload/', '/upload/fl_attachment/', $this->cloudinary_url);
    }

    /** Si può rigenerare solo a render concluso (bene o male) — stesso motivo di VideoMemoriale::modificabile(). */
    public function modificabile(): bool
    {
        return in_array($this->stato, [StatoReel::Pronto, StatoReel::Errore], true);
    }

    public function inElaborazione(): void
    {
        $this->forceFill(['stato' => StatoReel::InElaborazione])->save();
    }

    public function segnaPronto(string $cloudinaryUrl, string $cloudinaryPublicId, ?string $outputPath): void
    {
        $this->forceFill([
            'stato' => StatoReel::Pronto,
            'cloudinary_url' => $cloudinaryUrl,
            'cloudinary_public_id' => $cloudinaryPublicId,
            'output_path' => $outputPath,
            'messaggio_errore' => null,
        ])->save();
    }

    public function segnaErrore(string $messaggio): void
    {
        $this->forceFill([
            'stato' => StatoReel::Errore,
            'messaggio_errore' => $messaggio,
        ])->save();
    }
}
