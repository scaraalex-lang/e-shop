<?php

use App\Http\Controllers\Gestione\AccessoController;
use App\Http\Controllers\Gestione\PannelloController;
use App\Http\Controllers\Gestione\SlideController;
use App\Http\Middleware\VerificaAccessoGestione;
use App\Models\HomeSlide;
use Illuminate\Support\Facades\Route;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\Category;

Route::get('/', function () {
    $hero = Product::with('primaryImage')->where('sku', 'COR-MET-ORO')->first();

    // Slide del carosello d'apertura: gestite da /gestione/slide, non nel codice.
    $slide = HomeSlide::attive()->get();

    $evidenza = Product::with('category', 'primaryImage')
        ->where('is_active', true)
        ->whereIn('sku', ['COR-PRL-CHA', 'COR-VTR-ROS', 'ROS-BRC-CRI-BLU'])
        ->orderByRaw("FIELD(sku, 'COR-PRL-CHA', 'COR-VTR-ROS', 'ROS-BRC-CRI-BLU')")
        ->get();

    return view('home', compact('hero', 'slide', 'evidenza'));
})->name('home');

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
 | ============ DASHBOARD OPERATIVA ============
 | Governo della vetrina: slide della home e pratiche aperte.
 | FASE 1: accesso con password condivisa (config/gestione.php). Quando
 | Commerce porterà gli account staff, il gate va sostituito dall'auth vera.
 */
Route::prefix('gestione')->name('gestione.')->group(function () {

    Route::get('entra', [AccessoController::class, 'mostra'])->name('entra');
    Route::post('entra', [AccessoController::class, 'entra'])
        ->middleware('throttle:5,1')->name('entra.post');
    Route::post('esci', [AccessoController::class, 'esci'])->name('esci');

    Route::middleware(VerificaAccessoGestione::class)->group(function () {
        Route::get('/', [PannelloController::class, 'index'])->name('pannello');
        Route::get('pratiche', [PannelloController::class, 'pratiche'])->name('pratiche');

        Route::get('slide', [SlideController::class, 'index'])->name('slide.index');
        Route::get('slide/nuova', [SlideController::class, 'create'])->name('slide.create');
        Route::post('slide', [SlideController::class, 'store'])->name('slide.store');
        Route::get('slide/{slide}/modifica', [SlideController::class, 'edit'])->name('slide.edit');
        Route::put('slide/{slide}', [SlideController::class, 'update'])->name('slide.update');
        Route::delete('slide/{slide}', [SlideController::class, 'destroy'])->name('slide.destroy');

        Route::post('slide/{slide}/attiva', [SlideController::class, 'attiva'])->name('slide.attiva');
        Route::post('slide/{slide}/sposta/{direzione}', [SlideController::class, 'sposta'])
            ->whereIn('direzione', ['su', 'giu'])->name('slide.sposta');
    });
});
