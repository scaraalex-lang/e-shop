<?php

namespace Modules\VideoBook\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\VideoBook\Models\Libro;
use Modules\VideoBook\Models\Pagina;

/**
 * Chiama il proxy Python (stesso di TributeVideo/ReelSocial, nuovo endpoint
 * /render-book) e aggiorna lo stato del video del libro.
 *
 * Stesso schema di [[\Modules\TributeVideo\Jobs\GeneraVideoMemoriale]]:
 * `tries = 1` (un render fallito va rivisto a mano, non rilanciato alla
 * cieca), timeout sotto quello del worker (`eshop-queue-worker.service`,
 * `--timeout=1800`). Niente audio/nome/citazione da passare: il libro non
 * ha card di apertura/chiusura, solo le foto.
 */
class GeneraVideoBook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const LARGHEZZA = 1920;

    private const ALTEZZA = 1080;

    public int $timeout = 1500;

    public int $tries = 1;

    public function __construct(private readonly int $libroId)
    {
    }

    public function handle(): void
    {
        $libro = Libro::with(['pagine.foto'])->find($this->libroId);
        $video = $libro?->video;
        if (! $libro || ! $video) {
            return;
        }

        $video->inElaborazione();

        // Solo le pagine popolate, foto in ordine pagina -> slot: decisione
        // presa esplicitamente ("genera il video delle sole pagine
        // popolate") — le pagine vuote create dal passo "quante pagine" ma
        // mai riempite non diventano scene vuote nel video.
        $foto = $libro->paginePopolate()
            ->flatMap(fn (Pagina $p) => $p->foto->sortBy('slot'))
            ->map(fn ($f) => [
                'path' => Storage::disk('public')->path($f->path),
                'durata' => $f->durata_secondi,
                'zoom' => true,
            ])
            ->values()
            ->all();

        if (! $foto) {
            $video->segnaErrore('Nessuna pagina popolata: carica almeno una foto prima di generare il video.');

            return;
        }

        $outputPath = Storage::disk('public')->path('videobook/render/'.$libro->id.'.mp4');

        $payload = [
            'token' => 'libro-'.$libro->id,
            'photos' => $foto,
            'width' => self::LARGHEZZA,
            'height' => self::ALTEZZA,
            'output_path' => $outputPath,
        ];

        try {
            // Timeout sotto quello del worker: deve fallire prima che il
            // worker uccida il processo, così l'errore finisce sul record
            // invece di perdersi silenziosamente.
            $response = Http::timeout(1450)->post('http://127.0.0.1:5003/render-book', $payload);
        } catch (\Throwable $e) {
            $video->segnaErrore('Proxy non raggiungibile: '.$e->getMessage());

            return;
        }

        if (! $response->successful()) {
            $video->segnaErrore($response->json('message') ?? "Errore proxy (HTTP {$response->status()})");

            return;
        }

        $dati = $response->json();
        $video->segnaPronto($dati['cloudinary_url'] ?? '', $dati['cloudinary_public_id'] ?? '');
    }

    public function failed(\Throwable $e): void
    {
        $video = Libro::find($this->libroId)?->video;
        $video?->segnaErrore($e->getMessage());
    }
}
