<?php

namespace Modules\VideoBook\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\VideoBook\Http\Controllers\Concerns\ControllaAccessoLibro;
use Modules\VideoBook\Jobs\GeneraVideoBook;
use Modules\VideoBook\Models\Libro;

/**
 * Il passo finale dell'impaginatore: genera il video dalle pagine popolate
 * e ne espone lo stato per il polling dell'editor (fetch, non meta-refresh:
 * editor.blade.php è già tutto guidato da fetch(), a differenza delle
 * pagine classiche di TributeVideo/ReelSocial).
 */
class VideoController extends Controller
{
    use ControllaAccessoLibro;

    public function genera(Request $request, Libro $libro)
    {
        $this->assicuraProprio($request, $libro);

        if ($libro->pagine()->has('foto')->doesntExist()) {
            return response()->json(['error' => 'Nessuna pagina popolata: carica almeno una foto prima di generare il video.'], 422);
        }

        // Il video esistente si controlla PRIMA di crearne uno nuovo: un
        // Video appena creato ha già stato di default 'coda' (vedi
        // migration), quindi firstOrCreate() da solo risulterebbe sempre
        // "già in elaborazione" al primo giro.
        $video = $libro->video;
        if ($video && $video->inCorso()) {
            return response()->json(['error' => 'Il video è già in elaborazione.'], 422);
        }
        $video ??= $libro->video()->create([]);

        $video->inCoda();
        GeneraVideoBook::dispatch($libro->id);

        return response()->json(['success' => true, 'video' => $this->videoPerFrontend($video, $this->libroScaricabile($request, $libro))]);
    }

    public function stato(Request $request, Libro $libro)
    {
        $this->assicuraProprio($request, $libro);

        $video = $libro->video;

        return response()->json(['video' => $video ? $this->videoPerFrontend($video, $this->libroScaricabile($request, $libro)) : null]);
    }

    /** Il link di solo streaming (`url`) è sempre incluso, l'anteprima non è mai bloccata: solo `download_url` dipende da $scaricabile. */
    private function videoPerFrontend($video, bool $scaricabile): array
    {
        return [
            'stato' => $video->stato->value,
            'in_corso' => $video->inCorso(),
            'url' => $video->cloudinary_url,
            'download_url' => $scaricabile ? $video->downloadUrl() : null,
            'messaggio_errore' => $video->messaggio_errore,
        ];
    }
}
