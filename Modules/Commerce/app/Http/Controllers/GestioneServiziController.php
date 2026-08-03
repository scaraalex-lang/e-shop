<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Commerce\Models\ServizioEditor;

/**
 * Il catalogo servizi (ricordini/manifesti/necrologi): righe fisse, solo
 * costo in crediti e attivo/non attivo si modificano da qui.
 */
class GestioneServiziController extends Controller
{
    public function index(): View
    {
        return view('commerce::gestione.servizi.index', [
            'servizi' => ServizioEditor::orderBy('codice')->get(),
        ]);
    }

    public function update(Request $request, ServizioEditor $servizio): RedirectResponse
    {
        $dati = $request->validate([
            'costo_crediti' => ['required', 'integer', 'min:0'],
            'costo_crediti_a_termine' => ['nullable', 'integer', 'min:0'],
            'attivo' => ['nullable', 'boolean'],
        ]);

        $servizio->update([
            'costo_crediti' => $dati['costo_crediti'],
            'costo_crediti_a_termine' => $dati['costo_crediti_a_termine'] ?? null,
            'attivo' => (bool) ($dati['attivo'] ?? false),
        ]);

        return redirect()
            ->route('gestione.servizi.index')
            ->with('stato', "{$servizio->etichetta}: aggiornato.");
    }
}
