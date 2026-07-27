<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Catalog\Http\Requests\KitRequest;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\KitComponente;
use Modules\Catalog\Models\Product;

/**
 * Kit componibili: un prodotto venduto come un altro, ma composto scegliendo
 * più articoli dal magazzino invece di venderne uno solo. Diverso dal kit "a
 * soglia" del trigesimo (`is_kit`), che resta un prodotto singolo con un
 * conteggio di pezzi inclusi — qui la composizione è di articoli diversi.
 */
class GestioneKitController extends Controller
{
    public function index(): View
    {
        return view('catalog::gestione.kit.index', [
            'kit' => Product::query()
                ->where('is_componibile', true)
                ->with(['category', 'componenti.componente'])
                ->orderBy('name')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('catalog::gestione.kit.create', [
            'categorie' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(KitRequest $request): RedirectResponse
    {
        $kit = Product::create($request->datiKit());

        return redirect()
            ->route('gestione.kit.show', $kit)
            ->with('stato', "Kit \"{$kit->name}\" creato: ora componilo scegliendo gli articoli.");
    }

    public function show(Product $kit): View
    {
        abort_unless($kit->is_componibile, 404);

        return view('catalog::gestione.kit.show', [
            'kit' => $kit->load('componenti.componente'),
            'articoli' => Product::query()
                ->where('is_active', true)
                ->where('id', '!=', $kit->id)
                ->where('is_componibile', false)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function componentiStore(Request $request, Product $kit): RedirectResponse
    {
        abort_unless($kit->is_componibile, 404);

        $dati = $request->validate([
            'componente_product_id' => ['required', 'exists:products,id'],
            'quantita' => ['required', 'integer', 'min:1'],
        ], [
            'componente_product_id.required' => 'Scegli un articolo da aggiungere.',
        ]);

        abort_if((int) $dati['componente_product_id'] === $kit->id, 422, 'Un kit non può contenere se stesso.');

        $esistente = $kit->componenti()->where('componente_product_id', $dati['componente_product_id'])->first();

        if ($esistente) {
            $esistente->update(['quantita' => $esistente->quantita + $dati['quantita']]);
        } else {
            $kit->componenti()->create($dati);
        }

        return redirect()
            ->route('gestione.kit.show', $kit)
            ->with('stato', 'Articolo aggiunto al kit.');
    }

    public function componentiDestroy(Product $kit, KitComponente $componente): RedirectResponse
    {
        abort_unless($componente->kit_product_id === $kit->id, 404);

        $componente->delete();

        return redirect()
            ->route('gestione.kit.show', $kit)
            ->with('stato', 'Articolo tolto dal kit.');
    }
}
