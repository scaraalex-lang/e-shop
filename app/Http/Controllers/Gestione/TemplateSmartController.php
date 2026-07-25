<?php

namespace App\Http\Controllers\Gestione;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Memorial\Models\RicordinoTemplate;

/**
 * Quale impaginazione usa il Designer Smart, formato per formato.
 *
 * Da telefono non si sceglie il layout: lo decide qui chi gestisce, una volta,
 * e vale per tutte le prenotazioni. Nel designer completo restano tutti.
 */
class TemplateSmartController extends Controller
{
    public function index(): View
    {
        return view('gestione.template-smart', [
            'formati'   => array_keys(config('photoprint.formati')),
            'template'  => RicordinoTemplate::orderBy('formato')
                ->orderByDesc('is_predefinito')->orderBy('sort_order')->get()
                ->groupBy('formato'),
        ]);
    }

    public function aggiorna(Request $request): RedirectResponse
    {
        $formati = array_keys(config('photoprint.formati'));

        $dati = $request->validate([
            'scelta'   => ['required', 'array'],
            'scelta.*' => ['nullable', 'integer', 'exists:ricordino_templates,id'],
        ]);

        DB::transaction(function () use ($dati, $formati) {
            foreach ($formati as $formato) {
                $id = $dati['scelta'][$formato] ?? null;
                if (! $id) {
                    continue;
                }

                // uno solo per formato: prima si azzera, poi si segna il scelto
                RicordinoTemplate::where('formato', $formato)->update(['is_smart_default' => false]);
                RicordinoTemplate::where('formato', $formato)->whereKey($id)
                    ->update(['is_smart_default' => true]);
            }
        });

        return redirect()->route('gestione.template-smart')
            ->with('ok', 'Impaginazione del Designer Smart aggiornata.');
    }
}
