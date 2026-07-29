<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
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
                ->with('righe')
                ->latest()
                ->paginate(10),
        ]);
    }

    /**
     * Il punto d'ingresso per cominciare un ordine: servizio (crediti, solo
     * agenzia), prodotto singolo (vetrina) o kit (prodotto composto, oggi
     * solo il kit trigesimo — vedi Product::is_kit/is_componibile).
     *
     * I crediti/servizi sono un percorso da agenzia: prima stavano nella
     * pagina "Le pratiche" (Studio ricordini), ma lì si va per riprendere
     * una lavorazione già aperta, non per iniziarne una — spostato qui.
     */
    public function nuovo(Request $request): View
    {
        $utente = $request->user();
        $agenzia = $utente->agenzia;

        return view('commerce::ordini.nuovo', [
            'creditiSaldo' => $agenzia?->creditiSaldo(),
            'prodottoCrediti' => $agenzia ? Product::active()->where('sku', 'SRV-CREDITI-100')->first() : null,
            'servizi' => $agenzia ? ServizioEditor::attivi()->get() : null,
            'kit' => Product::active()
                ->where(fn ($q) => $q->where('is_kit', true)->orWhere('is_componibile', true))
                ->with('category', 'primaryImage')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    /**
     * Attiva uno o più servizi editor (ricordini/manifesti/necrologi) su un
     * ordine nuovo, pagati in crediti — vedi AttivaServiziOrdine.
     */
    public function attivaServizi(AttivaServiziOrdineRequest $request, AttivaServiziOrdine $attiva): RedirectResponse
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
            return redirect()
                ->route('ordini.nuovo')
                ->with('stato', "Servono {$esito['richiesti']} crediti, ne avete {$esito['saldo']}: mancano {$esito['mancano']}.");
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
