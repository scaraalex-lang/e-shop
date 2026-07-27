<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Catalog\Http\Requests\CategoriaRequest;
use Modules\Catalog\Models\Category;

/**
 * Gestione delle categorie da /gestione: prima di questo si creavano solo
 * da seeder o da tinker, come i prodotti prima della Fase 1.
 *
 * Niente "elimina": products.category_id è restrictOnDelete, cancellare
 * romperebbe i prodotti collegati. Si disattiva, come i prodotti.
 */
class GestioneCategorieController extends Controller
{
    public function index(): View
    {
        return view('catalog::gestione.categorie.index', [
            'categorie' => Category::with('parent')->orderBy('sort_order')->orderBy('name')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('catalog::gestione.categorie.create', [
            'categorieRadice' => Category::whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }

    public function store(CategoriaRequest $request): RedirectResponse
    {
        $categoria = Category::create($request->datiCategoria());

        $this->salvaImmagine($request, $categoria);

        return redirect()
            ->route('gestione.categorie.edit', $categoria)
            ->with('stato', "Categoria \"{$categoria->name}\" creata.");
    }

    public function edit(Category $categoria): View
    {
        return view('catalog::gestione.categorie.edit', [
            'categoria' => $categoria,
            'categorieRadice' => Category::whereNull('parent_id')->where('id', '!=', $categoria->id)->orderBy('name')->get(),
        ]);
    }

    public function update(CategoriaRequest $request, Category $categoria): RedirectResponse
    {
        $categoria->update($request->datiCategoria());

        $this->salvaImmagine($request, $categoria);

        return redirect()
            ->route('gestione.categorie.edit', $categoria)
            ->with('stato', "Categoria \"{$categoria->name}\" aggiornata.");
    }

    private function salvaImmagine(Request $request, Category $categoria): void
    {
        if (! $request->hasFile('immagine')) {
            return;
        }

        if ($categoria->image) {
            Storage::disk('public')->delete($categoria->image);
        }

        $categoria->update(['image' => $request->file('immagine')->store('categories', 'public')]);
    }
}
