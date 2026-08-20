<?php

namespace Modules\TributeVideo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\TributeVideo\Jobs\GeneraVideoMemoriale;
use Modules\TributeVideo\Models\VideoMemoriale;

/**
 * Pagina staff `/gestione/video-memoriale`: genera un video vero senza
 * passare da carrello/Stripe, per validare la pipeline (proxy Python +
 * coda + Cloudinary) prima di agganciarla al checkout reale.
 *
 * Testo libero apposta (nome/date/citazione), non un Defunto reale: l'MVP
 * di test non deve toccare dati di Memorial. `defunto_id`/`ordine_id`
 * restano null, pronti per l'integrazione vera in un secondo giro.
 */
class TestController extends Controller
{
    private const DISK_DIR = 'tributevideo';

    public function index()
    {
        $recenti = VideoMemoriale::latest()->limit(10)->get();

        return view('tributevideo::gestione.index', ['recenti' => $recenti]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome_completo' => ['required', 'string', 'max:255'],
            'date' => ['nullable', 'string', 'max:60'],
            'citazione' => ['nullable', 'string', 'max:1000'],
            'foto' => ['required', 'array', 'min:1'],
            'foto.*' => ['image', 'max:12288'],   // 12 MB, come il Foto Manager
            'testo' => ['nullable', 'array'],
            'testo.*' => ['nullable', 'string', 'max:180'],
            'posizione' => ['nullable', 'array'],
            'posizione.*' => ['nullable', 'in:alto,centro,basso'],
            'durata' => ['nullable', 'array'],
            'durata.*' => ['nullable', 'integer', 'min:1', 'max:15'],
            'zoom' => ['nullable', 'array'],
            'zoom.*' => ['nullable', 'boolean'],
            'audio' => ['nullable', 'file', 'mimes:mp3,wav,m4a,ogg', 'max:20480'], // 20 MB
        ], [
            'foto.required' => 'Serve almeno una fotografia.',
            'foto.*.image' => 'Uno dei file non è un\'immagine.',
            'foto.*.max' => 'Una foto supera i 12 MB: ridimensionala e riprova.',
            'audio.mimes' => 'Formato audio non riconosciuto (mp3, wav, m4a, ogg).',
            'audio.max' => 'L\'audio supera i 20 MB.',
        ]);

        $token = VideoMemoriale::nuovoToken();

        $audioPath = null;
        if ($request->hasFile('audio')) {
            $audioPath = $request->file('audio')->store(self::DISK_DIR.'/'.$token.'/audio', 'public');
        }

        // In transazione: se il salvataggio di una foto fallisce a metà
        // (es. permessi, disco pieno) non deve restare un video "fantasma"
        // in coda senza foto e senza Job — è successo davvero in test.
        $video = DB::transaction(function () use ($request, $token, $audioPath) {
            $video = VideoMemoriale::create([
                'token' => $token,
                'nome_completo' => $request->string('nome_completo'),
                'date' => $request->input('date'),
                'citazione' => $request->input('citazione'),
                'audio_path' => $audioPath,
                'stato' => 'in_coda',
            ]);

            foreach ($request->file('foto') as $ordine => $file) {
                $path = $file->store(self::DISK_DIR.'/'.$token.'/foto', 'public');
                // Didascalia opzionale letta per indice: non ci si fida della
                // lunghezza degli array paralleli mandati dal client.
                $testo = trim((string) $request->input("testo.$ordine")) ?: null;
                $video->foto()->create([
                    'path' => $path,
                    'ordine' => $ordine,
                    'testo' => $testo,
                    'testo_posizione' => $testo ? $request->input("posizione.$ordine") : null,
                    'durata_secondi' => $request->input("durata.$ordine"),
                    'zoom_attivo' => $request->boolean("zoom.$ordine", true),
                ]);
            }

            return $video;
        });

        GeneraVideoMemoriale::dispatch($video->id);

        return redirect()->route('gestione.video-memoriale.show', $video);
    }

    public function show(VideoMemoriale $videoMemoriale)
    {
        return view('tributevideo::gestione.show', ['video' => $videoMemoriale]);
    }
}
