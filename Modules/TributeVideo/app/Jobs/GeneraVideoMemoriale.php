<?php

namespace Modules\TributeVideo\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\TributeVideo\Models\VideoMemoriale;

/**
 * Chiama il proxy Python (moviepy + Cloudinary) e aggiorna lo stato del video.
 *
 * Primo Job asincrono del progetto: gira sotto `eshop-queue-worker.service`
 * (`queue:work database --timeout=1800`). Il timeout del Job resta sotto
 * quello del worker apposta, per non farsi ammazzare a metà scrittura DB.
 *
 * `tries = 1`: un render fallito va rivisto a mano, non rilanciato alla
 * cieca su una pipeline che costa minuti di CPU e un upload Cloudinary.
 */
class GeneraVideoMemoriale implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(private readonly int $videoMemorialeId)
    {
    }

    public function handle(): void
    {
        $video = VideoMemoriale::find($this->videoMemorialeId);
        if (! $video) {
            return;
        }

        $video->inElaborazione();

        // Ogni foto porta con sé la propria didascalia/durata/zoom, letti
        // dalle colonne dell'editor timeline (Modules\TributeVideo\Enums\PosizioneDidascalia).
        $foto = $video->foto()->orderBy('ordine')->get()
            ->map(fn ($f) => [
                'path' => Storage::disk('public')->path($f->path),
                'testo' => $f->testo,
                'posizione' => $f->testo_posizione?->value,
                'durata' => $f->durata_secondi,
                'zoom' => $f->zoom_attivo,
            ])
            ->all();

        $outputPath = Storage::disk('public')->path('tributevideo/output/'.$video->token.'.mp4');

        $payload = [
            'token' => $video->token,
            'photos' => $foto,
            'audio' => $video->audio_path ? Storage::disk('public')->path($video->audio_path) : null,
            'nome_completo' => $video->nome_completo,
            'date' => $video->date,
            'citazione' => $video->citazione,
            'output_path' => $outputPath,
        ];

        try {
            // Timeout sotto quello del worker (1800s): deve fallire prima
            // che il worker uccida il processo, così l'errore finisce sul
            // record invece che perdersi.
            $response = Http::timeout(1750)->post('http://127.0.0.1:5003/render', $payload);
        } catch (\Throwable $e) {
            $video->segnaErrore('Proxy non raggiungibile: '.$e->getMessage());

            return;
        }

        if (! $response->successful()) {
            $video->segnaErrore($response->json('message') ?? "Errore proxy (HTTP {$response->status()})");

            return;
        }

        $dati = $response->json();
        $video->segnaPronto(
            $dati['cloudinary_url'] ?? '',
            $dati['cloudinary_public_id'] ?? '',
            $dati['local_path'] ?? null,
        );
    }

    public function failed(\Throwable $e): void
    {
        $video = VideoMemoriale::find($this->videoMemorialeId);
        $video?->segnaErrore($e->getMessage());
    }
}
