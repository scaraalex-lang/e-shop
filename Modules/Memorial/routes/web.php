<?php

use Illuminate\Support\Facades\Route;
use Modules\Memorial\Http\Controllers\MemorialController;
use Modules\Memorial\Http\Controllers\NecrologiController;
use Modules\Memorial\Http\Controllers\NecrologioPubblicoController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('memorials', MemorialController::class)->names('memorial');
});

/*
|--------------------------------------------------------------------------
| Necrologi — lato agenzia
|--------------------------------------------------------------------------
| Non passano dall'ordine: un'agenzia fa necrologi tutte le settimane e
| compra un kit trigesimale ogni tanto. Sono lo strumento, non il prodotto.
*/
Route::middleware('auth')->prefix('account/necrologi')->name('necrologi.')->group(function () {
    Route::get('/', [NecrologiController::class, 'index'])->name('index');
    Route::get('nuovo', [NecrologiController::class, 'create'])->name('nuovo');
    Route::post('/', [NecrologiController::class, 'store'])->name('salva');

    Route::get('{necrologio}', [NecrologiController::class, 'edit'])->name('modifica');
    Route::patch('{necrologio}', [NecrologiController::class, 'update'])->name('aggiorna');

    Route::post('{necrologio}/consenso', [NecrologiController::class, 'consenso'])->name('consenso');
    Route::post('{necrologio}/revoca', [NecrologiController::class, 'revoca'])->name('revoca');
    Route::post('{necrologio}/pubblica', [NecrologiController::class, 'pubblica'])->name('pubblica');
    Route::post('{necrologio}/ritira', [NecrologiController::class, 'ritira'])->name('ritira');
});

/*
|--------------------------------------------------------------------------
| Il necrologio pubblico
|--------------------------------------------------------------------------
| /ricordi/{agenzia}/{percorso} — si legge, si condivide, non si indovina.
| Il nome dell'agenzia nel percorso è la sua vetrina: ogni condivisione
| porta in giro il suo nome.
*/
Route::get('ricordi/{agenzia}/{percorso}', [NecrologioPubblicoController::class, 'show'])
    ->name('necrologio');
