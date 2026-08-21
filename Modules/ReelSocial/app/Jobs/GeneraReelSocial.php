<?php

namespace Modules\ReelSocial\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\ReelSocial\Models\Reel;
use Modules\SocialStory\Models\StoriaSocial;
use Modules\TributeVideo\Models\VideoMemoriale;

/**
 * Chiama il nuovo endpoint /concat del proxy TributeVideo (porta 5003, VPS,
 * fuori dal repo git): concatena la copertina della Storia Social col Video
 * Memoriale già renderizzato, in un unico mp4 verticale.
 *
 * Stesso schema di Modules\TributeVideo\Jobs\GeneraVideoMemoriale: `tries=1`
 * (un errore va rivisto a mano), timeout sotto quello del worker.
 *
 * Path locali passati al proxy, non URL: copertina e video sono già sullo
 * stesso disco condiviso (storage/app/public), niente da scaricare — vedi
 * Reel::creaReel() nel controller per la provenienza dei due path.
 */
class GeneraReelSocial implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(private readonly int $reelId)
    {
    }

    public function handle(): void
    {
        $reel = Reel::find($this->reelId);
        if (! $reel) {
            return;
        }

        $reel->inElaborazione();

        $storia = StoriaSocial::find($reel->storia_social_id);
        $video = VideoMemoriale::find($reel->video_memoriale_id);

        if (! $storia?->anteprima || ! $video?->token) {
            $reel->segnaErrore('Copertina o video sorgente non più disponibili.');

            return;
        }

        $payload = [
            'token' => $reel->token,
            'cover_path' => Storage::disk('public')->path($storia->anteprima),
            'video_path' => Storage::disk('public')->path('tributevideo/output/'.$video->token.'.mp4'),
            'video_cloudinary_url' => $video->cloudinary_url,
            'output_path' => Storage::disk('public')->path('reels/output/'.$reel->token.'.mp4'),
        ];

        try {
            // Timeout sotto quello del worker (1800s), stesso motivo di
            // GeneraVideoMemoriale: deve fallire prima che il worker uccida
            // il processo, così l'errore finisce sul record invece che perdersi.
            $response = Http::timeout(1750)->post('http://127.0.0.1:5003/concat', $payload);
        } catch (\Throwable $e) {
            $reel->segnaErrore('Proxy non raggiungibile: '.$e->getMessage());

            return;
        }

        if (! $response->successful()) {
            $reel->segnaErrore($response->json('message') ?? "Errore proxy (HTTP {$response->status()})");

            return;
        }

        $dati = $response->json();
        $reel->segnaPronto(
            $dati['cloudinary_url'] ?? '',
            $dati['cloudinary_public_id'] ?? '',
            $dati['local_path'] ?? null,
        );
    }

    public function failed(\Throwable $e): void
    {
        $reel = Reel::find($this->reelId);
        $reel?->segnaErrore($e->getMessage());
    }
}
