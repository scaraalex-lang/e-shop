<?php

use App\Http\Controllers\GestioneContenutiController;
use App\Http\Controllers\GestioneMenuController;
use App\Http\Controllers\GestionePreghiereController;
use App\Http\Controllers\ProdottoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\Category;

Route::get('/', function () {
    $hero = Product::with('primaryImage')->where('sku', 'COR-MET-ORO')->first();

    $evidenza = Product::with('category', 'primaryImage')
        ->where('is_active', true)
        ->where('is_featured', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    return view('home', compact('hero', 'evidenza'));
});

// Pagina di prova del design system MemorAI
Route::get('/styleguide', function () {
    $prodotti = Product::with('category', 'primaryImage')
        ->where('is_active', true)
        ->whereIn('sku', ['COR-PRL-BIA', 'COR-LEG-MAR', 'ROS-BRC-CRI-BLU', 'TRG-KIT-50'])
        ->orderByRaw("FIELD(sku, 'COR-PRL-BIA', 'COR-LEG-MAR', 'ROS-BRC-CRI-BLU', 'TRG-KIT-50')")
        ->get();

    $hero = Product::with('primaryImage')->where('sku', 'COR-MET-ORO')->first();

    return view('styleguide', compact('prodotti', 'hero'));
})->name('styleguide');

// Pagina categoria: prodotti della categoria (e delle sue sottocategorie)
Route::get('/categoria/{slug}', function (string $slug) {
    $categoria = Category::where('slug', $slug)->where('is_active', true)->firstOrFail();

    // include i prodotti delle sottocategorie attive
    $ids = $categoria->children()->where('is_active', true)->pluck('id')
        ->push($categoria->id);

    $prodotti = Product::with('category', 'primaryImage')
        ->whereIn('category_id', $ids)
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->get();

    return view('categoria', compact('categoria', 'prodotti'));
})->name('categoria');

// Scheda prodotto: il catalogo descrive, Commerce stabilisce il prezzo.
Route::get('/prodotto/{slug}', [ProdottoController::class, 'show'])->name('prodotto');

/*
|--------------------------------------------------------------------------
| Area account (privati e agenzie)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('account')->group(function () {
    Route::view('/', 'account.index')->name('account');

    Route::get('profilo', [ProfileController::class, 'edit'])->name('account.profilo');
    Route::patch('profilo', [ProfileController::class, 'update'])->name('account.profilo.update');
    Route::delete('profilo', [ProfileController::class, 'destroy'])->name('account.profilo.destroy');
});

/*
|--------------------------------------------------------------------------
| Area staff: menu e footer del sito pubblico
|--------------------------------------------------------------------------
| A livello applicazione, non dentro un modulo: è contenuto del layout
| condiviso (resources/views/layouts/app.blade.php), non del catalogo.
*/
Route::middleware(['auth', 'staff'])->prefix('gestione')->name('gestione.')->group(function () {
    Route::get('menu', [GestioneMenuController::class, 'index'])->name('menu.index');
    Route::get('menu/nuova', [GestioneMenuController::class, 'create'])->name('menu.create');
    Route::post('menu', [GestioneMenuController::class, 'store'])->name('menu.store');
    Route::get('menu/{voce}/modifica', [GestioneMenuController::class, 'edit'])->name('menu.edit');
    Route::put('menu/{voce}', [GestioneMenuController::class, 'update'])->name('menu.update');
    Route::delete('menu/{voce}', [GestioneMenuController::class, 'destroy'])->name('menu.destroy');

    Route::get('contenuti', [GestioneContenutiController::class, 'edit'])->name('contenuti.edit');
    Route::put('contenuti', [GestioneContenutiController::class, 'update'])->name('contenuti.update');

    // Archivio preghiere: la galleria che si apre nel Ricordino Designer.
    Route::get('preghiere', [GestionePreghiereController::class, 'index'])->name('preghiere.index');
    Route::get('preghiere/nuova', [GestionePreghiereController::class, 'create'])->name('preghiere.create');
    Route::post('preghiere', [GestionePreghiereController::class, 'store'])->name('preghiere.store');
    Route::get('preghiere/{preghiera}/modifica', [GestionePreghiereController::class, 'edit'])->name('preghiere.edit');
    Route::put('preghiere/{preghiera}', [GestionePreghiereController::class, 'update'])->name('preghiere.update');
    Route::delete('preghiere/{preghiera}', [GestionePreghiereController::class, 'destroy'])->name('preghiere.destroy');
    Route::post('preghiere/{preghiera}/attiva', [GestionePreghiereController::class, 'attiva'])->name('preghiere.attiva');
});

require __DIR__.'/auth.php';
