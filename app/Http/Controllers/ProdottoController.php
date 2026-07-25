<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Prezzi\Listino;

/**
 * Scheda prodotto della vetrina.
 *
 * Sta al livello applicazione e non dentro Catalog perché mette insieme due
 * moduli: il catalogo che descrive l'articolo e Commerce che ne stabilisce il
 * prezzo per chi guarda. La dipendenza fra moduli va Catalog → Commerce, non
 * al contrario: farla passare di qui la tiene nel verso giusto.
 */
class ProdottoController extends Controller
{
    public function __construct(private Listino $listino) {}

    public function show(Request $request, string $slug): View
    {
        $prodotto = Product::with('category', 'images')
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        $utente = $request->user();

        // Per un kit la quantità di partenza è il numero di pezzi compresi:
        // proporre "1" avrebbe poco senso, il kit non si vende a ricordino.
        $quantitaIniziale = $prodotto->is_kit ? max($prodotto->included_units ?? 1, 1) : 1;

        $correlati = Product::with('category', 'primaryImage')
            ->active()
            ->where('category_id', $prodotto->category_id)
            ->whereKeyNot($prodotto->id)
            ->orderBy('sort_order')
            ->take(3)
            ->get();

        // Gli scaglioni si mostrano solo a chi può usarli, col prezzo già
        // calcolato: la vista non deve fare conti.
        $scaglioni = $this->listino->condizioniRiservate($utente)
            ? $this->listino->scaglioniDi($prodotto)->map(fn ($scaglione) => [
                'da' => $scaglione->quantita_minima,
                'sconto' => $scaglione->scontoLeggibile(),
                'prezzo' => $this->listino->perRiga($prodotto, $scaglione->quantita_minima, $utente)->scontato,
            ])
            : collect();

        return view('prodotto', [
            'prodotto' => $prodotto,
            'prezzo' => $this->listino->perRiga($prodotto, $quantitaIniziale, $utente),
            'quantitaIniziale' => $quantitaIniziale,
            'scaglioni' => $scaglioni,
            'correlati' => $correlati,
        ]);
    }
}
