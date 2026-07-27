<?php

namespace App\Http\Controllers;

use App\Enums\ZonaMenu;
use App\Models\VoceMenu;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Le voci del menu principale e delle colonne del footer: prima erano array
 * PHP scritti a mano in layouts/app.blade.php (vedi il View Composer in
 * AppServiceProvider che oggi le legge da qui).
 */
class GestioneMenuController extends Controller
{
    public function index(): View
    {
        return view('gestione.menu.index', [
            'perZona' => VoceMenu::orderBy('sort_order')->get()->groupBy('zona'),
            'zone' => ZonaMenu::cases(),
        ]);
    }

    public function create(): View
    {
        return view('gestione.menu.create', ['zone' => ZonaMenu::cases()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $voce = VoceMenu::create($this->valida($request));

        return redirect()
            ->route('gestione.menu.index')
            ->with('stato', "Voce \"{$voce->etichetta}\" creata.");
    }

    public function edit(VoceMenu $voce): View
    {
        return view('gestione.menu.edit', ['voce' => $voce, 'zone' => ZonaMenu::cases()]);
    }

    public function update(Request $request, VoceMenu $voce): RedirectResponse
    {
        $voce->update($this->valida($request));

        return redirect()
            ->route('gestione.menu.index')
            ->with('stato', "Voce \"{$voce->etichetta}\" aggiornata.");
    }

    public function destroy(VoceMenu $voce): RedirectResponse
    {
        $etichetta = $voce->etichetta;
        $voce->delete();

        return redirect()
            ->route('gestione.menu.index')
            ->with('stato', "Voce \"{$etichetta}\" eliminata.");
    }

    private function valida(Request $request): array
    {
        $dati = $request->validate([
            'zona' => ['required', 'in:'.implode(',', array_column(ZonaMenu::cases(), 'value'))],
            'etichetta' => ['required', 'string', 'max:120'],
            'url' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ], [], [
            'zona' => 'la zona',
            'etichetta' => 'l\'etichetta',
            'url' => 'l\'indirizzo',
            'sort_order' => 'l\'ordine',
        ]);

        $dati['is_active'] = $request->boolean('is_active');

        return $dati;
    }
}
