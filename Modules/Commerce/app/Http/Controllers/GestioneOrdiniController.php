<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;

/**
 * Gli ordini di tutta la piattaforma, visti da chi produce e spedisce —
 * a differenza di OrdiniController (il cliente vede solo i propri).
 */
class GestioneOrdiniController extends Controller
{
    public function index(Request $request): View
    {
        $stato = StatoOrdine::tryFrom((string) $request->query('stato'));
        $provenienza = $request->query('provenienza'); // 'b2b' | 'b2c' | null

        $ordini = Ordine::query()
            ->with(['user', 'agenzia', 'righe'])
            ->when($stato, fn ($q) => $q->where('stato', $stato))
            ->when($provenienza === 'b2b', fn ($q) => $q->whereNotNull('agenzia_id'))
            ->when($provenienza === 'b2c', fn ($q) => $q->whereNull('agenzia_id'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('commerce::gestione.ordini.index', [
            'ordini' => $ordini,
            'statoAttivo' => $stato,
            'provenienzaAttiva' => $provenienza,
            'conteggi' => Ordine::query()
                ->selectRaw('stato, count(*) as totale')
                ->groupBy('stato')
                ->pluck('totale', 'stato'),
        ]);
    }

    public function show(Ordine $ordine): View
    {
        $ordine->load(['user', 'agenzia', 'righe', 'servizi.servizioEditor']);

        // Solo per gli ordini di servizi digitali (Manifesti/Necrologi/
        // Ricordini): chi lavora l'ordine vede subito di quale foto si
        // tratta, senza dover aprire la Scheda Defunto a parte.
        $defunto = $ordine->defunto_id ? Defunto::find($ordine->defunto_id) : null;
        $fotoPath = $defunto?->fotoPrincipalePath();

        return view('commerce::gestione.ordini.show', [
            'ordine' => $ordine,
            'defunto' => $defunto,
            'fotoUrl' => $fotoPath ? '/storage/'.ltrim($fotoPath, '/') : null,
        ]);
    }

    public function spedisci(Request $request, Ordine $ordine): RedirectResponse
    {
        $dati = $request->validate([
            'corriere' => ['required', 'string', 'max:60'],
            'tracking_numero' => ['required', 'string', 'max:100'],
        ], [], [
            'corriere' => 'il corriere',
            'tracking_numero' => 'il numero di tracking',
        ]);

        $ordine->spedisci($dati['corriere'], $dati['tracking_numero']);

        return redirect()
            ->route('gestione.ordini.show', $ordine)
            ->with('stato', "Ordine {$ordine->numero} segnato come spedito.");
    }

    public function segnaConsegnato(Ordine $ordine): RedirectResponse
    {
        $ordine->passaA(StatoOrdine::Consegnato);

        return redirect()
            ->route('gestione.ordini.show', $ordine)
            ->with('stato', "Ordine {$ordine->numero} segnato come consegnato.");
    }
}
