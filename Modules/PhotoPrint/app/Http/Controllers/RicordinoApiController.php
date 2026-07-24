<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Ricordino;
use Modules\Memorial\Models\Santo;

/**
 * Endpoint del Ricordino Designer (FASE 1).
 *
 * Collega il designer ai dati Memorial: galleria santi condivisa, salvataggio
 * del ricordino legato al defunto, e registrazione del consenso GDPR in-app.
 * Protetti dallo stesso guard token del Foto Manager [[VerifyStudioToken]].
 */
class RicordinoApiController extends Controller
{
    private const SANTI_DIR = 'santi';

    // ---- Galleria santi --------------------------------------------------

    /** Elenco santi per il modale del designer. */
    public function santiIndex()
    {
        $santi = Santo::orderBy('nome')->get()->map(fn (Santo $s) => [
            'url'  => $s->url(),
            'name' => $s->nome,
        ]);

        return response()->json($santi);
    }

    /** Upload di un nuovo santo nella galleria condivisa. */
    public function santiStore(Request $request)
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'max:8192'],
            'name'  => ['required', 'string', 'max:120'],
        ]);

        $path = $request->file('image')->store(self::SANTI_DIR, 'public');
        $santo = Santo::create(['nome' => $validated['name'], 'path' => $path]);

        return response()->json(['success' => true, 'url' => $santo->url()]);
    }

    // ---- Salvataggio ricordino ------------------------------------------

    /**
     * Salva (o aggiorna) il ricordino di un defunto: stato canvas fronte/retro,
     * formato e anteprime. È il legame concreto defunto ↔ ricordino.
     */
    public function salvaRicordino(Request $request, Defunto $defunto)
    {
        $fronte = $this->decodeCanvas($request->input('canvas_fronte'));
        $retro  = $this->decodeCanvas($request->input('canvas_retro'));

        $ricordino = $defunto->ricordini()->firstOrNew([]);
        $ricordino->fill([
            'formato'          => (string) $request->input('format', '7x10'),
            'fronte'           => $fronte,
            'retro'            => $retro,
            'stato'            => 'bozza',
            'anteprima_fronte' => $this->storeDataUrl($request->input('preview')),
            'anteprima_retro'  => $this->storeDataUrl($request->input('preview_retro')),
        ]);
        $defunto->ricordini()->save($ricordino);

        return response()->json(['success' => true, 'ricordino_id' => $ricordino->id]);
    }

    // ---- Consenso GDPR ---------------------------------------------------

    /**
     * Registra il consenso GDPR (in-app) per il defunto: chi autorizza l'uso
     * di immagine e dati, in che relazione, con nota facoltativa.
     */
    public function salvaGdpr(Request $request, Defunto $defunto)
    {
        $validated = $request->validate([
            'autorizzato_da' => ['required', 'string', 'max:150'],
            'parentela'      => ['nullable', 'string', 'max:80'],
            'note'           => ['nullable', 'string', 'max:1000'],
        ]);

        $defunto->autorizzaGdpr(
            $validated['autorizzato_da'],
            $validated['parentela'] ?? null,
            $validated['note'] ?? null,
        );

        return response()->json([
            'success'        => true,
            'consenso'       => true,
            'autorizzato_da' => $defunto->gdpr_autorizzato_da,
            'parentela'      => $defunto->gdpr_parentela,
            'autorizzato_at' => $defunto->gdpr_autorizzato_at?->format('d/m/Y H:i'),
        ]);
    }

    // ---- Template (FASE 1: solo elenco vuoto, sistema completo in Fase 2) --

    public function templatesIndex()
    {
        return response()->json([]);
    }

    // ---- Helper ----------------------------------------------------------

    private function decodeCanvas($raw): ?array
    {
        if (is_array($raw)) {
            return $raw;
        }
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : null;
        }

        return null;
    }

    /** Salva un'anteprima base64 nello storage e ritorna il path (o null). */
    private function storeDataUrl($dataUrl): ?string
    {
        if (! is_string($dataUrl) || ! str_starts_with($dataUrl, 'data:')) {
            return null;
        }
        $comma = strpos($dataUrl, ',');
        if ($comma === false) {
            return null;
        }
        $binary = base64_decode(substr($dataUrl, $comma + 1), true);
        if ($binary === false) {
            return null;
        }
        $path = 'ricordini/anteprime/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
