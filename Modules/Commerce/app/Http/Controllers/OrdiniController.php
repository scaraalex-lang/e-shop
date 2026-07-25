<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Commerce\Models\Ordine;

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
        ]);
    }
}
