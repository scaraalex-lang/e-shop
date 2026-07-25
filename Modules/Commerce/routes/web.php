<?php

use Illuminate\Support\Facades\Route;
use Modules\Commerce\Http\Controllers\GestioneAgenzieController;
use Modules\Commerce\Http\Controllers\RegistrazioneAgenziaController;

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
    Route::get('agenzie/{agenzia}', [GestioneAgenzieController::class, 'show'])->name('agenzie.show');

    Route::post('agenzie/{agenzia}/approva', [GestioneAgenzieController::class, 'approva'])->name('agenzie.approva');
    Route::post('agenzie/{agenzia}/rifiuta', [GestioneAgenzieController::class, 'rifiuta'])->name('agenzie.rifiuta');
    Route::post('agenzie/{agenzia}/sospendi', [GestioneAgenzieController::class, 'sospendi'])->name('agenzie.sospendi');
});
