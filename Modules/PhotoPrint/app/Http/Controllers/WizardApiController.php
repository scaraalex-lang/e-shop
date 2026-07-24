<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    /** Cartella pubblica per le immagini del foto manager. */
    private const DISK_DIR = 'photoprint-demo';

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

        return response()->json([
            'success' => true,
            'photo'   => [
                'id'            => (int) (microtime(true) * 1000) % 1000000,
                'url'           => $this->absoluteUrl($request, $path),
                'tipo'          => (string) $request->input('tipo', 'elaborata_ai'),
                'is_principale' => (bool) $request->input('is_principale', false),
            ],
        ]);
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
