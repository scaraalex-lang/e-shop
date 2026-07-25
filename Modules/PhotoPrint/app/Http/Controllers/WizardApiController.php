<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\PhotoPrint\Models\FotoPratica;
use Modules\PhotoPrint\Servizi\LavorazioneCorrente;

/**
 * Endpoint AI del Foto Manager (FASE 1 — verifica funzionale del wizard).
 *
 * I 3 endpoint bfl/* sono un PROXY verso il microservizio Python vivo sul VPS
 * (bfl-proxy.service, http://127.0.0.1:5000) — servizio CONDIVISO in produzione
 * con memoraiengine.com: qui non lo modifichiamo, ci si appoggia soltanto.
 * La chiave BFL resta nel proxy, mai in Laravel.
 *
 * upload-temp / salva-url gestiscono lo storage locale delle immagini così che
 * il proxy possa scaricarle via URL assoluto e il risultato finisca in galleria.
 */
class WizardApiController extends Controller
{
    /** Base del microservizio BFL sul VPS. */
    private const BFL_BASE = 'http://127.0.0.1:5000';

    /**
     * Host da cui il proxy scarica le immagini dell'eshop. È uno static server
     * separato (threaded) dal dev server Laravel: così il download del proxy NON
     * rientra sulla stessa :8000 single-worker (che si bloccherebbe). In Fase 2,
     * dietro nginx+php-fpm, questa riscrittura non servirà.
     */
    private const PROXY_FETCH_BASE = 'http://127.0.0.1:8010';

    /**
     * Cartella pubblica per le immagini del foto manager.
     *
     * Era `photoprint-demo`: adesso ci finiscono le fotografie vere dei
     * clienti, e chiamarle "demo" invitava a cancellarle. I file di prova
     * restano dov'erano, nessun percorso già scritto si sposta.
     */
    private const DISK_DIR = 'photoprint';

    // ---- Proxy BFL -------------------------------------------------------

    public function enhance(Request $request)
    {
        return $this->forward('/enhance', [
            'image_url' => $this->rewriteForProxy($request->input('image_url')),
        ]);
    }

    public function outpaint(Request $request)
    {
        return $this->forward('/outpaint', array_merge(
            $request->only(['top', 'bottom', 'left', 'right']),
            ['image_url' => $this->rewriteForProxy($request->input('image_url'))],
        ));
    }

    public function removeBg(Request $request)
    {
        return $this->forward('/remove-bg', array_merge(
            $request->only(['background_prompt']),
            ['image_url' => $this->rewriteForProxy($request->input('image_url'))],
        ));
    }

    /**
     * Riscrive un URL dell'eshop verso lo static server da cui il proxy scarica.
     * URL esterni (es. risultati BFL su api.bfl.ai) restano intatti.
     */
    private function rewriteForProxy(?string $url): ?string
    {
        if (! $url) {
            return $url;
        }
        // Path relativo → assoluto sullo static server.
        if (str_starts_with($url, '/')) {
            return self::PROXY_FETCH_BASE . $url;
        }
        // Qualsiasi file /storage/ dell'eshop (a prescindere da host/porta:
        // localhost, IP pubblico, :8000...) va scaricato dallo static server :8010.
        // Gli URL esterni (risultati BFL su delivery.*.bfl.ai) restano intatti.
        $parts = parse_url($url);
        $path = $parts['path'] ?? '';
        if (str_starts_with($path, '/storage/')) {
            return self::PROXY_FETCH_BASE . $path
                . (isset($parts['query']) ? '?' . $parts['query'] : '');
        }

        return $url;
    }

    /**
     * Inoltra la richiesta al proxy BFL e ne restituisce la risposta tale quale.
     * Timeout ampio: le pipeline Flux richiedono polling (fino a ~120s).
     */
    private function forward(string $path, array $payload)
    {
        try {
            $resp = Http::timeout(180)
                ->acceptJson()
                ->post(self::BFL_BASE . $path, $payload);

            return response()->json($resp->json(), $resp->status());
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Proxy BFL non raggiungibile: ' . $e->getMessage(),
            ], 502);
        }
    }

    // ---- Storage immagini ------------------------------------------------

    /**
     * Carica una fotografia dalla galleria del Foto Manager.
     *
     * È il primo gesto di chi apre una lavorazione con la galleria vuota:
     * senza questo endpoint il bottone "carica" rispondeva 404 e l'editor
     * diceva soltanto "upload fallito".
     */
    public function upload(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'max:12288'],   // 12 MB
        ], [
            'photo.image' => 'Il file non è un\'immagine.',
            'photo.max' => 'L\'immagine supera i 12 MB: ridimensionala e riprova.',
        ]);

        $path = $request->file('photo')->store(self::DISK_DIR.'/originali', 'public');

        // La prima foto della pratica diventa la principale: è quella che il
        // Designer si aspetta di trovare sul canvas.
        $foto = $this->registra($path, 'originale', principale: $this->primaDellaPratica());

        return response()->json([
            'success' => true,
            'photo' => $this->perFrontend($request, $path, 'originale', $foto),
        ]);
    }

    /**
     * Salva sulla pratica quello che c'è sul canvas: il ritaglio, la rotazione,
     * la versione scelta come principale.
     */
    public function salva(Request $request)
    {
        $binary = $this->decodeDataUrl((string) $request->input('image', ''));

        if ($binary === null) {
            return response()->json(['error' => 'Immagine non valida'], 400);
        }

        $path = self::DISK_DIR.'/pratica/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $binary);

        $tipo = (string) $request->input('tipo', 'ritagliata');
        $principale = (bool) $request->input('is_principale', false);

        $foto = $this->registra($path, $tipo, $principale);

        return response()->json([
            'success' => true,
            'photo' => $this->perFrontend($request, $path, $tipo, $foto, $principale),
        ]);
    }

    /**
     * Toglie una foto dalla pratica: sia la riga sia il file.
     */
    public function elimina(Request $request, int $foto)
    {
        $ordine = app(LavorazioneCorrente::class)->ordine();

        // Si cancella solo dentro la propria lavorazione: gli id sono
        // progressivi e senza questo controllo si toccherebbero le foto altrui.
        $riga = FotoPratica::where('id', $foto)
            ->when($ordine, fn ($q) => $q->where('ordine_id', $ordine->id))
            ->first();

        if (! $riga || ! $ordine) {
            return response()->json(['error' => 'Fotografia non trovata'], 404);
        }

        Storage::disk('public')->delete($riga->path);
        $riga->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Salva un'immagine base64 (data URL) e ne ritorna l'URL assoluto,
     * così il proxy BFL può scaricarla.
     */
    public function uploadTemp(Request $request)
    {
        $dataUrl = (string) $request->input('image_data', '');
        $binary = $this->decodeDataUrl($dataUrl);
        if ($binary === null) {
            return response()->json(['error' => 'Immagine non valida'], 400);
        }

        $path = self::DISK_DIR . '/tmp/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($path, $binary);

        // Se si sta lavorando un ordine, la foto entra nel registro della
        // pratica: prima finiva su disco e nessuno se ne ricordava più.
        $this->registra($path, 'originale', principale: true);

        return response()->json(['url' => $this->absoluteUrl($request, $path)]);
    }

    /**
     * Scarica il risultato AI (URL BFL, effimero) nello storage locale e lo
     * restituisce come oggetto foto pronto per la galleria.
     */
    public function salvaUrl(Request $request)
    {
        $sourceUrl = (string) $request->input('image_url', '');
        if ($sourceUrl === '') {
            return response()->json(['error' => 'URL mancante'], 400);
        }

        try {
            $binary = Http::timeout(60)->get($sourceUrl)->body();
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Download risultato fallito'], 502);
        }

        $path = self::DISK_DIR . '/ai/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($path, $binary);

        $tipo = (string) $request->input('tipo', 'elaborata_ai');
        $principale = (bool) $request->input('is_principale', false);

        // Il risultato dell'elaborazione è quello che andrà in stampa: va
        // legato alla pratica, altrimenti si perde chiudendo il browser.
        $foto = $this->registra($path, $tipo, principale: true);

        return response()->json([
            'success' => true,
            'photo'   => [
                'id'            => $foto?->id ?? (int) (microtime(true) * 1000) % 1000000,
                'url'           => $this->absoluteUrl($request, $path),
                'tipo'          => $tipo,
                'is_principale' => $foto ? true : $principale,
            ],
        ]);
    }

    /** Vero se la pratica non ha ancora nessuna fotografia. */
    private function primaDellaPratica(): bool
    {
        $ordine = app(LavorazioneCorrente::class)->ordine();

        return $ordine && ! FotoPratica::where('ordine_id', $ordine->id)->exists();
    }

    /**
     * La forma che si aspetta il JS degli editor.
     *
     * Fuori da una lavorazione non c'è una riga a database: si ripiega su un
     * id effimero, così staff e agenzie possono comunque provare gli editor.
     */
    private function perFrontend(Request $request, string $path, string $tipo, ?FotoPratica $foto, bool $principale = false): array
    {
        return [
            'id' => $foto?->id ?? (int) (microtime(true) * 1000) % 1000000,
            'url' => $this->absoluteUrl($request, $path),
            'tipo' => $tipo,
            'is_principale' => $foto?->is_principale ?? $principale,
        ];
    }

    /**
     * Registra la foto nella pratica dell'ordine in lavorazione, se ce n'è
     * uno. Fuori da una lavorazione (staff che prova gli editor) non fa
     * nulla: non c'è una pratica a cui appendere il file.
     */
    private function registra(string $path, string $tipo, bool $principale = false): ?FotoPratica
    {
        $ordine = app(LavorazioneCorrente::class)->ordine();

        if (! $ordine) {
            return null;
        }

        $foto = FotoPratica::create([
            'ordine_id' => $ordine->id,
            'path' => $path,
            'tipo' => $tipo,
        ]);

        if ($principale) {
            $foto->rendiPrincipale();
        }

        return $foto;
    }

    // ---- Helper ----------------------------------------------------------

    private function decodeDataUrl(string $dataUrl): ?string
    {
        if (! str_starts_with($dataUrl, 'data:')) {
            return null;
        }
        $comma = strpos($dataUrl, ',');
        if ($comma === false) {
            return null;
        }
        $binary = base64_decode(substr($dataUrl, $comma + 1), true);

        return $binary === false ? null : $binary;
    }

    /**
     * URL assoluto costruito dall'host REALE della richiesta (non da APP_URL,
     * che qui è http://localhost). Così l'immagine è raggiungibile sia dal
     * browser dell'utente sia — via rewriteForProxy — dal proxy BFL.
     */
    private function absoluteUrl(Request $request, string $storagePath): string
    {
        return $request->getSchemeAndHttpHost() . '/storage/' . ltrim($storagePath, '/');
    }
}
