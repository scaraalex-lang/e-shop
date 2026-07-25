<?php

namespace App\Http\Controllers\Gestione;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Memorial\Models\Preghiera;

/**
 * Archivio dei testi di preghiera: è la galleria che si apre nel Designer
 * Smart. Chi prenota dal telefono sceglie di qui, non scrive.
 */
class PreghiereController extends Controller
{
    public function index(): View
    {
        return view('gestione.preghiere.index', [
            'preghiere' => Preghiera::orderBy('categoria')->orderBy('sort_order')->orderBy('id')->get()
                ->groupBy(fn (Preghiera $p) => $p->categoria ?: 'Senza categoria'),
        ]);
    }

    public function create(): View
    {
        return view('gestione.preghiere.form', [
            'preghiera' => new Preghiera([
                'is_active'  => true,
                'sort_order' => (int) Preghiera::max('sort_order') + 10,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $preghiera = Preghiera::create($this->validati($request));

        return redirect()->route('gestione.preghiere.index')
            ->with('ok', "«{$preghiera->titolo}» aggiunta all'archivio.");
    }

    public function edit(Preghiera $preghiera): View
    {
        return view('gestione.preghiere.form', compact('preghiera'));
    }

    public function update(Request $request, Preghiera $preghiera): RedirectResponse
    {
        $preghiera->update($this->validati($request));

        return redirect()->route('gestione.preghiere.index')
            ->with('ok', "«{$preghiera->titolo}» aggiornata.");
    }

    public function destroy(Preghiera $preghiera): RedirectResponse
    {
        $titolo = $preghiera->titolo;
        $preghiera->delete();

        return redirect()->route('gestione.preghiere.index')
            ->with('ok', "«{$titolo}» rimossa dall'archivio.");
    }

    /** Mostra/nasconde il testo nella galleria dello Smart. */
    public function attiva(Preghiera $preghiera): RedirectResponse
    {
        $preghiera->update(['is_active' => ! $preghiera->is_active]);

        return back()->with('ok', $preghiera->is_active
            ? "«{$preghiera->titolo}» torna nella galleria."
            : "«{$preghiera->titolo}» nascosta dalla galleria.");
    }

    private function validati(Request $request): array
    {
        $dati = $request->validate([
            'titolo'     => ['required', 'string', 'max:120'],
            'testo'      => ['required', 'string', 'max:1200'],
            'categoria'  => ['nullable', 'string', 'max:60'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $dati['is_active'] = $request->boolean('is_active');

        return $dati;
    }
}
