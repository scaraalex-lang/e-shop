<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Models\RigaCarrello;
use Modules\Commerce\Prezzi\Listino;
use Modules\Commerce\Servizi\GestoreCarrello;

class CarrelloController extends Controller
{
    public function __construct(
        private GestoreCarrello $carrelli,
        private Listino $listino,
    ) {}

    public function index(Request $request): View
    {
        $carrello = $this->carrelli->corrente();
        $utente = $request->user();

        $agenzia = $utente?->eAgenziaApprovata() ? $utente->agenzia : null;

        return view('commerce::carrello.index', [
            'carrello' => $carrello,
            'conto' => $carrello?->conto($this->listino, $utente),
            'agenzia' => $agenzia,
            'pezzi' => $carrello?->pezzi() ?? 0,
            'soloCrediti' => $carrello?->soloCrediti() ?? false,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $dati = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantita' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $prodotto = Product::active()->findOrFail($dati['product_id']);

        $this->carrelli->aggiungi($prodotto, $dati['quantita']);

        return redirect()
            ->route('carrello')
            ->with('stato', "{$prodotto->name}: aggiunto al carrello.");
    }

    public function update(Request $request, RigaCarrello $riga): RedirectResponse
    {
        $this->suaOppureNiente($riga);

        $dati = $request->validate([
            'quantita' => ['required', 'integer', 'min:0', 'max:100000'],
        ]);

        $this->carrelli->aggiorna($riga, $dati['quantita']);

        return redirect()->route('carrello');
    }

    public function destroy(RigaCarrello $riga): RedirectResponse
    {
        $this->suaOppureNiente($riga);

        $nome = $riga->product?->name;
        $riga->delete();

        return redirect()
            ->route('carrello')
            ->with('stato', $nome ? "{$nome}: tolto dal carrello." : 'Riga rimossa.');
    }

    /**
     * Una riga si tocca solo se sta nel carrello di chi sta chiedendo.
     *
     * Gli id delle righe sono progressivi e quindi indovinabili: senza questo
     * controllo chiunque potrebbe svuotare il carrello di un altro cambiando
     * un numero nell'indirizzo. 404 e non 403: l'esistenza della riga altrui
     * non è un'informazione da dare.
     */
    private function suaOppureNiente(RigaCarrello $riga): void
    {
        $carrello = $this->carrelli->corrente();

        abort_unless($carrello && $riga->carrello_id === $carrello->id, 404);
    }
}
