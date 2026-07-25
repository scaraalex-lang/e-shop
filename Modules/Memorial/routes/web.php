<?php

use Illuminate\Support\Facades\Route;
use Modules\Memorial\Http\Controllers\MemorialController;
use Modules\Memorial\Http\Controllers\PrenotazioneRicordinoController;

/*
 | Ingresso pubblico al flusso ricordini: dati del defunto (con consenso) e poi
 | via al Foto Manager. Pubblica come /studio/*, in attesa dell'area cliente
 | (Fase 2, dipende da Commerce).
 */
Route::get('/prenota/ricordino', [PrenotazioneRicordinoController::class, 'create'])
    ->name('prenota.ricordino');

Route::post('/prenota/ricordino', [PrenotazioneRicordinoController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('prenota.ricordino.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('memorials', MemorialController::class)->names('memorial');
});
