<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Commerce\Models\Ordine;
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

    public function show(Request $request, Ordine $ordine): View
    {
        // Un ordine si vede solo se è il proprio. 404 e non 403: l'esistenza
        // dell'ordine di un altro non è un'informazione da dare.
        abort_unless($ordine->user_id === $request->user()->id, 404);

        return view('commerce::ordini.show', [
            'ordine' => $ordine->load('righe.product.primaryImage'),
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
