<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Commerce\Models\AgenteVendita;

/**
 * Anagrafica degli agenti di vendita B2B: nessun login proprio, servono solo
 * per attribuire un'agenzia a chi la segue (vedi Agenzia::assegnaAgente).
 */
class GestioneAgentiController extends Controller
{
    public function index(): View
    {
        return view('commerce::gestione.agenti.index', [
            'agenti' => AgenteVendita::withCount('agenzie')->orderBy('nome')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('commerce::gestione.agenti.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dati = $this->valida($request);

        $agente = AgenteVendita::create($dati);

        return redirect()
            ->route('gestione.agenti.index')
            ->with('stato', "Agente {$agente->nome} creato.");
    }

    public function edit(AgenteVendita $agente): View
    {
        return view('commerce::gestione.agenti.edit', [
            'agente' => $agente->load('agenzie'),
        ]);
    }

    public function update(Request $request, AgenteVendita $agente): RedirectResponse
    {
        $agente->update($this->valida($request));

        return redirect()
            ->route('gestione.agenti.index')
            ->with('stato', "Agente {$agente->nome} aggiornato.");
    }

    public function destroy(AgenteVendita $agente): RedirectResponse
    {
        $nome = $agente->nome;
        $agente->agenzie()->each(fn ($agenzia) => $agenzia->assegnaAgente(null));
        $agente->delete();

        return redirect()
            ->route('gestione.agenti.index')
            ->with('stato', "Agente {$nome} eliminato.");
    }

    private function valida(Request $request): array
    {
        return $request->validate([
            'nome' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'note' => ['nullable', 'string', 'max:2000'],
        ], [], [
            'nome' => 'il nome',
            'email' => 'l\'email',
            'telefono' => 'il telefono',
        ]);
    }
}
