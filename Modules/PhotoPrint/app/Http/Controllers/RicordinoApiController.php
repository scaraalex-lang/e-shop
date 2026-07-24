<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Ricordino;
use Modules\Memorial\Models\RicordinoTemplate;
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
    private const ANTEPRIME_DIR = 'ricordini/anteprime';
    private const TEMPLATE_DIR = 'ricordini/template';

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

    // ---- Template di ricordino -------------------------------------------

    /**
     * Elenco dei template salvati. Le chiavi rispecchiano quelle attese dal JS
     * del designer (name/format/thumbnail/canvas_*), importato da memoraiengine.
     */
    public function templatesIndex()
    {
        $templates = RicordinoTemplate::inOrdineDiElenco()->get()->map(fn (RicordinoTemplate $t) => [
            'id'            => $t->id,
            'name'          => $t->nome,
            'format'        => $t->formato,
            'predefinito'   => $t->is_predefinito,
            'thumbnail'     => $t->anteprimaUrl(),
            'canvas_fronte' => $t->fronte,
            'canvas_retro'  => $t->retro,
        ]);

        return response()->json($templates);
    }

    /**
     * Salva il layout corrente come template.
     *
     * Il designer invia i canvas già ripuliti: testi personali riportati a
     * segnaposto e foto del defunto esclusa (vedi canvasTemplateJSON nel
     * blade). Qui si rifiuta solo un template senza alcun contenuto.
     */
    public function templatesStore(Request $request)
    {
        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:120'],
            'format' => ['nullable', 'string', 'in:7x10,6x9'],
        ]);

        $fronte = $this->decodeCanvas($request->input('canvas_fronte'));
        $retro  = $this->decodeCanvas($request->input('canvas_retro'));

        if (empty($fronte['objects']) && empty($retro['objects'])) {
            return response()->json(['error' => 'Il ricordino è vuoto: non c\'è niente da salvare come template.'], 422);
        }

        $template = RicordinoTemplate::create([
            'nome'      => $validated['name'],
            'formato'   => $validated['format'] ?? '7x10',
            'fronte'    => $fronte,
            'retro'     => $retro,
            'anteprima' => $this->storeDataUrl($request->input('thumbnail'), self::TEMPLATE_DIR),
        ]);

        return response()->json(['success' => true, 'id' => $template->id]);
    }

    /**
     * Aggiorna un template esistente col layout corrente (ed eventualmente lo
     * rinomina): è il "salva sopra" dopo aver ritoccato un template applicato.
     * I predefiniti MemorAI non si sovrascrivono, si duplicano.
     */
    public function templatesUpdate(Request $request, RicordinoTemplate $template)
    {
        if ($template->is_predefinito) {
            return response()->json(['error' => 'I template predefiniti MemorAI non si modificano: salvalo come nuovo template.'], 403);
        }

        $validated = $request->validate([
            'name'   => ['required', 'string', 'max:120'],
            'format' => ['nullable', 'string', 'in:7x10,6x9'],
        ]);

        $fronte = $this->decodeCanvas($request->input('canvas_fronte'));
        $retro  = $this->decodeCanvas($request->input('canvas_retro'));

        if (empty($fronte['objects']) && empty($retro['objects'])) {
            return response()->json(['error' => 'Il ricordino è vuoto: non c\'è niente da salvare come template.'], 422);
        }

        $anteprima = $this->storeDataUrl($request->input('thumbnail'), self::TEMPLATE_DIR);
        if ($anteprima && $template->anteprima) {
            Storage::disk('public')->delete($template->anteprima);   // via la vecchia
        }

        // fronte/retro si sovrascrivono anche se vuoti (svuotare un lato è una
        // modifica legittima); l'anteprima solo se ne è arrivata una nuova.
        $dati = [
            'nome'    => $validated['name'],
            'formato' => $validated['format'] ?? $template->formato,
            'fronte'  => $fronte,
            'retro'   => $retro,
        ];
        if ($anteprima) {
            $dati['anteprima'] = $anteprima;
        }
        $template->update($dati);

        return response()->json(['success' => true, 'id' => $template->id]);
    }

    /** Elimina un template dell'utente e la sua anteprima. I predefiniti restano. */
    public function templatesDestroy(RicordinoTemplate $template)
    {
        if ($template->is_predefinito) {
            return response()->json(['error' => 'I template predefiniti MemorAI non si possono eliminare.'], 403);
        }

        if ($template->anteprima) {
            Storage::disk('public')->delete($template->anteprima);
        }
        $template->delete();

        return response()->json(['success' => true]);
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
    private function storeDataUrl($dataUrl, string $dir = self::ANTEPRIME_DIR): ?string
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
        $path = trim($dir, '/') . '/' . Str::uuid() . '.jpg';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
