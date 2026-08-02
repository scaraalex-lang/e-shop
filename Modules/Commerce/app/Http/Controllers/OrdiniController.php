<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Http\Requests\AttivaServiziOrdineRequest;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Models\ServizioEditor;
use Modules\Commerce\Servizi\AttivaServiziOrdine;
use Modules\Memorial\Models\Ricordino;

/**
 * Gli ordini visti dal cliente: elenco e tracciamento.
 */
class OrdiniController extends Controller
{
    public function index(Request $request): View
    {
        return view('commerce::ordini.index', [
            'ordini' => Ordine::di($request->user())
                ->with('righe', 'servizi.servizioEditor')
                ->latest()
                ->paginate(10),
        ]);
    }

    /**
     * Il punto d'ingresso per cominciare un ordine: prodotto singolo
     * (vetrina) o kit (prodotto composto, oggi solo il kit trigesimo — vedi
     * Product::is_kit/is_componibile). Il percorso a crediti (servizi editor)
     * ha una pagina a sé, vedi servizi() — è l'operatività quotidiana
     * dell'agenzia, merita una voce di menu propria invece di stare
     * nascosto in fondo a "Nuovo ordine".
     */
    public function nuovo(Request $request): View
    {
        return view('commerce::ordini.nuovo', [
            'kit' => Product::active()
                ->where(fn ($q) => $q->where('is_kit', true)->orWhere('is_componibile', true))
                ->with('category', 'primaryImage')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    /**
     * Servizi a crediti (ricordini/manifesti/necrologi): il percorso da
     * agenzia più usato, prima incastonato dentro "Nuovo ordine".
     */
    public function servizi(Request $request): View
    {
        $agenzia = $request->user()->agenzia;

        return view('commerce::servizi.index', [
            'creditiSaldo' => $agenzia?->creditiSaldo(),
            'prodottoCrediti' => $agenzia ? Product::active()->where('sku', 'SRV-CREDITI-100')->first() : null,
            'servizi' => $agenzia ? ServizioEditor::attivi()->attivabiliSuOrdine()->get() : null,
        ]);
    }

    /**
     * Attiva uno o più servizi editor (ricordini/manifesti/necrologi) su un
     * ordine nuovo, pagati in crediti — vedi AttivaServiziOrdine.
     *
     * Risponde in JSON quando il form la chiama via fetch (progressive
     * enhancement): la pagina Servizi aggiorna il saldo a vista prima di
     * passare alla lavorazione, invece di sparire subito in un redirect.
     */
    public function attivaServizi(AttivaServiziOrdineRequest $request, AttivaServiziOrdine $attiva): RedirectResponse|JsonResponse
    {
        $utente = $request->user();
        $agenzia = $utente->eAgenziaApprovata() ? $utente->agenzia : null;

        abort_unless($agenzia, 404);

        $esito = $attiva->da(
            $utente,
            $agenzia,
            $request->codiciServizio(),
            $request->occasione(),
            $request->validated('numero_anniversario'),
        );

        if (is_array($esito)) {
            if ($request->wantsJson()) {
                return response()->json(['errore' => "Servono {$esito['richiesti']} crediti, ne avete {$esito['saldo']}: mancano {$esito['mancano']}."], 422);
            }

            return redirect()
                ->route('servizi')
                ->with('stato', "Servono {$esito['richiesti']} crediti, ne avete {$esito['saldo']}: mancano {$esito['mancano']}.");
        }

        if ($request->wantsJson()) {
            return response()->json([
                'saldo' => $agenzia->creditiSaldo(),
                'redirect' => route('lavorazione', $esito),
            ]);
        }

        return redirect()->route('lavorazione', $esito);
    }

    public function show(Request $request, Ordine $ordine): View
    {
        // Un ordine si vede solo se è il proprio. 404 e non 403: l'esistenza
        // dell'ordine di un altro non è un'informazione da dare.
        abort_unless($ordine->user_id === $request->user()->id, 404);

        return view('commerce::ordini.show', [
            'ordine' => $ordine->load('righe.product.primaryImage', 'righe.product.category', 'servizi.servizioEditor'),
            'ricordino' => $this->bozzaDi($ordine),
        ]);
    }

    /**
     * La bozza del ricordino di questo ordine, se la lavorazione è arrivata
     * a comporla.
     *
     * Commerce legge Memorial e non il contrario: il defunto è il ponte fra
     * ordine e ricordino (vedi CLAUDE.md), e Memorial non conosce gli ordini
     * se non come un id sciolto, quindi non si crea nessun anello.
     */
    private function bozzaDi(Ordine $ordine): ?Ricordino
    {
        if (! $ordine->defunto_id) {
            return null;
        }

        return Ricordino::where('defunto_id', $ordine->defunto_id)->latest()->first();
    }
}
