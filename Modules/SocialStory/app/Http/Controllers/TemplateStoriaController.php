<?php

namespace Modules\SocialStory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\SocialStory\Models\StoriaTemplate;

/**
 * Template di storia social, gestiti interamente dal designer (nessuna
 * pagina /gestione a parte) — stesso schema di
 * `NecrologiController::manifestoTemplates*()`: `agenzia_id` null è un
 * layout globale/curato (solo staff), un id è l'archivio della singola
 * agenzia. I privati (B2C) leggono ma non creano/modificano — non hanno un
 * concetto di "archivio proprio" in questo modulo.
 */
class TemplateStoriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $agenziaId = $request->user()?->agenzia?->id;

        // Niente ORDER BY in SQL: `layout` può crescere parecchio (vedi
        // [[bug-select-star-sort-memory]]), si ordina in PHP dopo il fetch.
        $templates = StoriaTemplate::visibiliPer($agenziaId)->get()
            ->sortByDesc('is_predefinito')->sortBy('sort_order')->values()
            ->map(fn (StoriaTemplate $t) => [
                'id' => $t->id,
                'nome' => $t->nome,
                'globale' => $t->agenzia_id === null,
                'editabile' => $this->appartieneA($request, $t),
                'anteprima' => $t->anteprimaUrl(),
                'layout' => $t->layout,
            ]);

        return response()->json($templates);
    }

    public function store(Request $request): JsonResponse
    {
        $proprietario = $this->proprietario($request);

        $dati = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'layout' => ['required', 'string'],
            'anteprima' => ['nullable', 'string'],
        ]);

        $layout = json_decode($dati['layout'], true);
        abort_if(! is_array($layout) || empty($layout['objects']), 422, 'La storia è vuota: non c\'è niente da salvare come template.');

        $template = StoriaTemplate::create([
            'nome' => $dati['nome'],
            'agenzia_id' => $proprietario,
            'layout' => $layout,
            'anteprima' => $this->salvaAnteprimaDataUrl($dati['anteprima'] ?? null),
        ]);

        return response()->json(['success' => true, 'id' => $template->id]);
    }

    public function update(Request $request, StoriaTemplate $template): JsonResponse
    {
        abort_unless($this->appartieneA($request, $template), 403, 'Questo template non è tuo.');

        $dati = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'layout' => ['required', 'string'],
            'anteprima' => ['nullable', 'string'],
        ]);

        $layout = json_decode($dati['layout'], true);
        abort_if(! is_array($layout) || empty($layout['objects']), 422, 'La storia è vuota: non c\'è niente da salvare come template.');

        $anteprima = $this->salvaAnteprimaDataUrl($dati['anteprima'] ?? null);
        if ($anteprima && $template->anteprima) {
            Storage::disk('public')->delete($template->anteprima);
        }

        $template->update(array_filter([
            'nome' => $dati['nome'],
            'layout' => $layout,
            'anteprima' => $anteprima,
        ], fn ($v) => $v !== null));

        return response()->json(['success' => true, 'id' => $template->id]);
    }

    public function destroy(Request $request, StoriaTemplate $template): JsonResponse
    {
        abort_unless($this->appartieneA($request, $template), 403, 'Questo template non è tuo.');

        if ($template->anteprima) {
            Storage::disk('public')->delete($template->anteprima);
        }
        $template->delete();

        return response()->json(['success' => true]);
    }

    /** null per lo staff (template globale), l'agenzia per un'agenzia — 403 per un privato B2C. */
    private function proprietario(Request $request): ?int
    {
        $utente = $request->user();
        if ($utente->eStaff()) {
            return null;
        }

        abort_unless($utente->agenzia !== null, 403, 'Solo staff e agenzie possono salvare template.');

        return $utente->agenzia->id;
    }

    private function appartieneA(Request $request, StoriaTemplate $template): bool
    {
        $utente = $request->user();

        return $template->agenzia_id === null
            ? (bool) $utente?->eStaff()
            : $utente?->agenzia?->id === $template->agenzia_id;
    }

    private function salvaAnteprimaDataUrl(?string $dataUrl): ?string
    {
        if (! $dataUrl || ! str_starts_with($dataUrl, 'data:')) {
            return null;
        }
        $comma = strpos($dataUrl, ',');
        if ($comma === false) {
            return null;
        }
        $binario = base64_decode(substr($dataUrl, $comma + 1), true);
        if ($binario === false) {
            return null;
        }
        $path = 'storia-templates/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $binario);

        return $path;
    }
}
