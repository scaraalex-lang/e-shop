<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Catalog\Http\Requests\ProdottoRequest;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

/**
 * Gestione del catalogo da /gestione: prima di questo, un prodotto si
 * caricava solo da seeder o da tinker.
 *
 * Non c'è un vero "elimina": un prodotto venduto resta referenziato dalle
 * righe ordine passate (che ne cristallizzano nome/sku/prezzo), quindi
 * sparire dal catalogo significa is_active=false, non una riga in meno.
 */
class GestioneProdottiController extends Controller
{
    public function index(Request $request): View
    {
        $categoria = $request->integer('categoria') ?: null;
        $stato = $request->query('stato');
        $ricerca = trim((string) $request->query('q'));

        $prodotti = Product::query()
            ->with(['category', 'primaryImage'])
            ->when($categoria, fn ($q) => $q->where('category_id', $categoria))
            ->when($stato === 'attivi', fn ($q) => $q->where('is_active', true))
            ->when($stato === 'disattivi', fn ($q) => $q->where('is_active', false))
            ->when($ricerca !== '', fn ($q) => $q->where(fn ($q2) => $q2
                ->where('name', 'like', "%{$ricerca}%")
                ->orWhere('sku', 'like', "%{$ricerca}%")))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('catalog::gestione.prodotti.index', [
            'prodotti' => $prodotti,
            'categorie' => Category::orderBy('name')->get(),
            'categoriaAttiva' => $categoria,
            'statoAttivo' => $stato,
            'ricerca' => $ricerca,
        ]);
    }

    public function create(): View
    {
        return view('catalog::gestione.prodotti.create', [
            'categorie' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ProdottoRequest $request): RedirectResponse
    {
        $dati = $request->datiProdotto();

        $prodotto = Product::create($dati);

        $this->applicaHeroUnico($dati, $prodotto);
        $this->salvaFoto($request, $prodotto);

        return redirect()
            ->route('gestione.prodotti.edit', $prodotto)
            ->with('stato', "Prodotto \"{$prodotto->name}\" creato.");
    }

    /**
     * Precedente/successivo nello stesso ordine alfabetico dell'elenco: così
     * lo staff scorre i prodotti per fare modifiche senza tornare ogni volta
     * all'indice. (name, id) come chiave composta perché il nome non è unico.
     */
    public function edit(Product $prodotto): View
    {
        $precedente = Product::query()
            ->where(fn ($q) => $q->where('name', '<', $prodotto->name)
                ->orWhere(fn ($q2) => $q2->where('name', $prodotto->name)->where('id', '<', $prodotto->id)))
            ->orderByDesc('name')->orderByDesc('id')
            ->first();

        $successivo = Product::query()
            ->where(fn ($q) => $q->where('name', '>', $prodotto->name)
                ->orWhere(fn ($q2) => $q2->where('name', $prodotto->name)->where('id', '>', $prodotto->id)))
            ->orderBy('name')->orderBy('id')
            ->first();

        return view('catalog::gestione.prodotti.edit', [
            'prodotto' => $prodotto->load('images'),
            'categorie' => Category::orderBy('name')->get(),
            'precedente' => $precedente,
            'successivo' => $successivo,
        ]);
    }

    public function update(ProdottoRequest $request, Product $prodotto): RedirectResponse
    {
        $dati = $request->datiProdotto();

        $prodotto->update($dati);

        $this->applicaHeroUnico($dati, $prodotto);
        $this->salvaFoto($request, $prodotto);

        return redirect()
            ->route('gestione.prodotti.edit', $prodotto)
            ->with('stato', "Prodotto \"{$prodotto->name}\" aggiornato.");
    }

    /** L'hero della home è uno solo: attivarlo su un prodotto lo spegne su tutti gli altri. */
    private function applicaHeroUnico(array $dati, Product $prodotto): void
    {
        if ($dati['is_hero'] ?? false) {
            Product::where('id', '!=', $prodotto->id)->where('is_hero', true)->update(['is_hero' => false]);
        }
    }

    /**
     * Le due foto vanno su due righe di ProductImage: quella di copertina
     * (is_primary, mostrata in vetrina e nelle griglie) e quella di
     * dettaglio (la galleria che il prodotto già mostra da sola, vedi
     * resources/views/prodotto.blade.php). Ricaricarle sostituisce il file
     * precedente, non ne aggiunge uno nuovo.
     */
    private function salvaFoto(Request $request, Product $prodotto): void
    {
        if ($request->hasFile('foto_catalogo')) {
            $this->sostituisciImmagine($prodotto, isPrimary: true, sortOrder: 0, file: $request->file('foto_catalogo'));
        }

        if ($request->hasFile('foto_zoom')) {
            $this->sostituisciImmagine($prodotto, isPrimary: false, sortOrder: 1, file: $request->file('foto_zoom'));
        }
    }

    private function sostituisciImmagine(Product $prodotto, bool $isPrimary, int $sortOrder, $file): void
    {
        $esistente = $prodotto->images()->where('is_primary', $isPrimary)->first();

        $path = $file->store('products', 'public');

        if ($esistente) {
            Storage::disk('public')->delete($esistente->path);
            $esistente->update(['path' => $path]);

            return;
        }

        $prodotto->images()->create([
            'path' => $path,
            'is_primary' => $isPrimary,
            'sort_order' => $sortOrder,
        ]);
    }
}
