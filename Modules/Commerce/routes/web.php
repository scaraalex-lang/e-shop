<?php

use Illuminate\Support\Facades\Route;
use Modules\Commerce\Http\Controllers\CarrelloController;
use Modules\Commerce\Http\Controllers\CheckoutController;
use Modules\Commerce\Http\Controllers\GestioneAgentiController;
use Modules\Commerce\Http\Controllers\GestioneAgenzieController;
use Modules\Commerce\Http\Controllers\OrdiniController;
use Modules\Commerce\Http\Controllers\RegistrazioneAgenziaController;

/*
|--------------------------------------------------------------------------
| Carrello
|--------------------------------------------------------------------------
| Aperto anche agli ospiti: si riempie liberamente, l'account serve al
| momento dell'ordine. Il carrello dell'ospite viene fuso in quello
| dell'account al primo accesso (UnisciCarrelloOspite).
*/
Route::get('carrello', [CarrelloController::class, 'index'])->name('carrello');
Route::post('carrello', [CarrelloController::class, 'store'])->name('carrello.aggiungi');
Route::patch('carrello/riga/{riga}', [CarrelloController::class, 'update'])->name('carrello.aggiorna');
Route::delete('carrello/riga/{riga}', [CarrelloController::class, 'destroy'])->name('carrello.rimuovi');

/*
|--------------------------------------------------------------------------
| Checkout e ordini
|--------------------------------------------------------------------------
| Qui l'account diventa obbligatorio: il carrello si riempie da ospiti, ma
| l'ordine ha bisogno di qualcuno a cui intestarlo. Chi arriva senza account
| viene mandato ad accedere e ritrova il carrello grazie a UnisciCarrelloOspite.
*/
Route::middleware('auth')->group(function () {
    Route::get('ordine/conferma', [CheckoutController::class, 'create'])->name('checkout');
    Route::post('ordine/conferma', [CheckoutController::class, 'store']);

    Route::get('account/ordini', [OrdiniController::class, 'index'])->name('ordini');
    Route::get('account/ordini/{ordine}', [OrdiniController::class, 'show'])->name('ordine');
});

/*
|--------------------------------------------------------------------------
| Registrazione del canale B2B
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('registrati/agenzia', [RegistrazioneAgenziaController::class, 'create'])
        ->name('registrazione.agenzia');

    Route::post('registrati/agenzia', [RegistrazioneAgenziaController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Area staff: approvazione delle agenzie
|--------------------------------------------------------------------------
| Primo pezzo di /gestione. Il middleware `staff` risponde 404 a chi non lo è.
*/
Route::middleware(['auth', 'staff'])->prefix('gestione')->name('gestione.')->group(function () {
    Route::get('agenzie', [GestioneAgenzieController::class, 'index'])->name('agenzie.index');
    Route::get('agenzie/nuova', [GestioneAgenzieController::class, 'create'])->name('agenzie.create');
    Route::post('agenzie', [GestioneAgenzieController::class, 'store'])->name('agenzie.store');
    Route::get('agenzie/{agenzia}', [GestioneAgenzieController::class, 'show'])->name('agenzie.show');

    Route::post('agenzie/{agenzia}/approva', [GestioneAgenzieController::class, 'approva'])->name('agenzie.approva');
    Route::post('agenzie/{agenzia}/rifiuta', [GestioneAgenzieController::class, 'rifiuta'])->name('agenzie.rifiuta');
    Route::post('agenzie/{agenzia}/sospendi', [GestioneAgenzieController::class, 'sospendi'])->name('agenzie.sospendi');
    Route::post('agenzie/{agenzia}/agente', [GestioneAgenzieController::class, 'assegnaAgente'])->name('agenzie.agente');
    Route::post('agenzie/{agenzia}/sconto', [GestioneAgenzieController::class, 'impostaSconto'])->name('agenzie.sconto');

    Route::get('agenti', [GestioneAgentiController::class, 'index'])->name('agenti.index');
    Route::get('agenti/nuovo', [GestioneAgentiController::class, 'create'])->name('agenti.create');
    Route::post('agenti', [GestioneAgentiController::class, 'store'])->name('agenti.store');
    Route::get('agenti/{agente}/modifica', [GestioneAgentiController::class, 'edit'])->name('agenti.edit');
    Route::put('agenti/{agente}', [GestioneAgentiController::class, 'update'])->name('agenti.update');
    Route::delete('agenti/{agente}', [GestioneAgentiController::class, 'destroy'])->name('agenti.destroy');
});
