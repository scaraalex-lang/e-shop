<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\CatalogController;
use Modules\Catalog\Http\Controllers\GestioneCategorieController;
use Modules\Catalog\Http\Controllers\GestioneKitController;
use Modules\Catalog\Http\Controllers\GestioneProdottiController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('catalogs', CatalogController::class)->names('catalog');
});

/*
|--------------------------------------------------------------------------
| Gestione prodotti (staff)
|--------------------------------------------------------------------------
| Stesso prefisso/alias di Modules/Commerce/routes/web.php: /gestione vive
| in più moduli, un gruppo per modulo.
*/
Route::middleware(['auth', 'staff'])->prefix('gestione')->name('gestione.')->group(function () {
    Route::get('prodotti', [GestioneProdottiController::class, 'index'])->name('prodotti.index');
    Route::get('prodotti/nuovo', [GestioneProdottiController::class, 'create'])->name('prodotti.create');
    Route::post('prodotti', [GestioneProdottiController::class, 'store'])->name('prodotti.store');
    Route::get('prodotti/{prodotto:id}/modifica', [GestioneProdottiController::class, 'edit'])->name('prodotti.edit');
    Route::put('prodotti/{prodotto:id}', [GestioneProdottiController::class, 'update'])->name('prodotti.update');

    Route::get('kit', [GestioneKitController::class, 'index'])->name('kit.index');
    Route::get('kit/nuovo', [GestioneKitController::class, 'create'])->name('kit.create');
    Route::post('kit', [GestioneKitController::class, 'store'])->name('kit.store');
    Route::get('kit/{kit:id}', [GestioneKitController::class, 'show'])->name('kit.show');
    Route::post('kit/{kit:id}/componenti', [GestioneKitController::class, 'componentiStore'])->name('kit.componenti.store');
    Route::delete('kit/{kit:id}/componenti/{componente}', [GestioneKitController::class, 'componentiDestroy'])->name('kit.componenti.destroy');

    Route::get('categorie', [GestioneCategorieController::class, 'index'])->name('categorie.index');
    Route::get('categorie/nuova', [GestioneCategorieController::class, 'create'])->name('categorie.create');
    Route::post('categorie', [GestioneCategorieController::class, 'store'])->name('categorie.store');
    Route::get('categorie/{categoria:id}/modifica', [GestioneCategorieController::class, 'edit'])->name('categorie.edit');
    Route::put('categorie/{categoria:id}', [GestioneCategorieController::class, 'update'])->name('categorie.update');
});
