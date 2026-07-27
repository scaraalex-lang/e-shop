<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\CatalogController;
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
});
