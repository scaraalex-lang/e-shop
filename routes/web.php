<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\Category;

Route::get('/', function () {
    $hero = Product::with('primaryImage')->where('sku', 'COR-MET-ORO')->first();

    $evidenza = Product::with('category', 'primaryImage')
        ->where('is_active', true)
        ->whereIn('sku', ['COR-PRL-CHA', 'COR-VTR-ROS', 'ROS-BRC-CRI-BLU'])
        ->orderByRaw("FIELD(sku, 'COR-PRL-CHA', 'COR-VTR-ROS', 'ROS-BRC-CRI-BLU')")
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

require __DIR__.'/auth.php';
