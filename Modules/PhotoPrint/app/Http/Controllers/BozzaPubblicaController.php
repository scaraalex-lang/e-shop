<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\RevisioneRicordino;

/**
 * La bozza vista dalla famiglia.
 *
 * Nessun account e nessuna password: il link è la credenziale. Chi lo riceve
 * sta attraversando un lutto — chiedergli di registrarsi per dire "va bene"
 * sarebbe una piccola crudeltà oltre che un ostacolo.
 *
 * Il link vale finché l'ordine è aperto: non una scadenza a data fissa,
 * perché un trigesimo può slittare.
 */
class BozzaPubblicaController extends Controller
{
    public function show(RevisioneRicordino $revisione): View
    {
        $this->soloSeAperta($revisione);

        $revisione->segnaVista();

        return view('photoprint::bozza.show', [
            'revisione' => $revisione,
            'ricordino' => $revisione->ricordino,
            'defunto' => $revisione->ricordino->defunto,
            'precedenti' => $revisione->ricordino->revisioni()
                ->whereNotNull('esito')
                ->whereKeyNot($revisione->id)
                ->get(),
        ]);
    }

    public function approva(RevisioneRicordino $revisione): RedirectResponse
    {
        $this->soloSeAperta($revisione);

        if ($revisione->inAttesa()) {
            $revisione->approva();
            $revisione->ricordino->registraRisposta($revisione);
        }

        return redirect()->route('bozza', $revisione->token);
    }

    public function chiediModifiche(Request $request, RevisioneRicordino $revisione): RedirectResponse
    {
        $this->soloSeAperta($revisione);

        $dati = $request->validate([
            'nota' => ['required', 'string', 'min:3', 'max:2000'],
        ], [
            'nota.required' => 'Scrivi cosa vorresti cambiare: anche poche parole bastano.',
        ]);

        if ($revisione->inAttesa()) {
            $revisione->chiediModifiche($dati['nota']);
            $revisione->ricordino->registraRisposta($revisione);
        }

        return redirect()->route('bozza', $revisione->token);
    }

    /**
     * Il link muore con l'ordine: quando la pratica è chiusa o annullata non
     * ha più senso che una pagina pubblica mostri i dati di un defunto.
     */
    private function soloSeAperta(RevisioneRicordino $revisione): void
    {
        $defunto = $revisione->ricordino?->defunto;

        abort_unless($defunto !== null, 404);

        // Nessun ordine collegato: è una pratica di prova dello staff, si vede.
        if (! $defunto->ordine_id) {
            return;
        }

        $ordine = Ordine::find($defunto->ordine_id);

        abort_unless($ordine && $ordine->aperto(), 404);
    }
}
