<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Commerce\Enums\StatoAgenzia;
use Modules\Commerce\Models\Agenzia;

/**
 * Area staff: approvazione manuale delle richieste di account agenzia.
 * Primo pezzo di /gestione.
 */
class GestioneAgenzieController extends Controller
{
    public function index(Request $request): View
    {
        $stato = StatoAgenzia::tryFrom((string) $request->query('stato'));

        $agenzie = Agenzia::query()
            ->with('user')
            ->when($stato, fn ($q) => $q->where('stato', $stato))
            ->orderByRaw("CASE WHEN stato = 'in_attesa' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('commerce::gestione.agenzie.index', [
            'agenzie' => $agenzie,
            'statoAttivo' => $stato,
            'conteggi' => Agenzia::query()
                ->selectRaw('stato, count(*) as totale')
                ->groupBy('stato')
                ->pluck('totale', 'stato'),
        ]);
    }

    public function show(Agenzia $agenzia): View
    {
        return view('commerce::gestione.agenzie.show', [
            'agenzia' => $agenzia->load('user'),
        ]);
    }

    public function approva(Request $request, Agenzia $agenzia): RedirectResponse
    {
        $dati = $request->validate([
            'note_interne' => ['nullable', 'string', 'max:2000'],
        ]);

        $agenzia->approva($request->user(), $dati['note_interne'] ?? null);

        return redirect()
            ->route('gestione.agenzie.show', $agenzia)
            ->with('stato', "Agenzia {$agenzia->ragione_sociale} approvata.");
    }

    public function rifiuta(Request $request, Agenzia $agenzia): RedirectResponse
    {
        $dati = $request->validate([
            'motivo_rifiuto' => ['required', 'string', 'max:1000'],
            'note_interne' => ['nullable', 'string', 'max:2000'],
        ]);

        $agenzia->rifiuta($request->user(), $dati['motivo_rifiuto'], $dati['note_interne'] ?? null);

        return redirect()
            ->route('gestione.agenzie.show', $agenzia)
            ->with('stato', "Richiesta di {$agenzia->ragione_sociale} non approvata.");
    }

    public function sospendi(Request $request, Agenzia $agenzia): RedirectResponse
    {
        $dati = $request->validate([
            'note_interne' => ['nullable', 'string', 'max:2000'],
        ]);

        $agenzia->sospendi($request->user(), $dati['note_interne'] ?? null);

        return redirect()
            ->route('gestione.agenzie.show', $agenzia)
            ->with('stato', "Agenzia {$agenzia->ragione_sociale} sospesa.");
    }
}
