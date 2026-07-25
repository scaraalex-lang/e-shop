<?php

use Illuminate\Support\Facades\Route;
use Modules\PhotoPrint\Http\Controllers\PhotoPrintController;
use Modules\PhotoPrint\Http\Controllers\WizardApiController;
use Modules\PhotoPrint\Http\Controllers\RicordinoApiController;
use Modules\PhotoPrint\Http\Middleware\AccessoStudio;

/*
 | Area studio: i due editor.
 |
 | Protetta dall'autenticazione vera (staff e agenzie approvate). Prima era
 | pubblica, con un token condiviso iniettato nella pagina: si leggeva dal
 | sorgente, quindi teneva fuori gli scanner ma non una persona.
 |
 | `auth` gestisce chi non ha fatto login: redirect alla pagina di accesso,
 | oppure 401 JSON per le chiamate degli editor, che si annunciano come XHR.
 */
Route::middleware(['auth', AccessoStudio::class])->group(function () {
    Route::get('/studio/foto', [PhotoPrintController::class, 'fotoManager'])->name('studio.foto');
    Route::get('/studio/ricordino', [PhotoPrintController::class, 'ricordinoDesigner'])->name('studio.ricordino');
});

/*
 | Endpoint AI del Foto Manager. I path /admin/api/... rispecchiano quelli
 | attesi dal frontend importato. I bfl/* proxano al microservizio Python vivo.
 |
 | Stesso guard delle pagine, più il rate-limit: la porta verso il proxy BFL
 | non deve essere abusabile nemmeno da un account legittimo. Il CSRF vale
 | anche qui: il wrapper su window.fetch dei due blade allega l'header.
 */
Route::prefix('admin/api')->middleware(['auth', AccessoStudio::class, 'throttle:30,1'])->group(function () {
    Route::post('bfl/enhance',   [WizardApiController::class, 'enhance']);
    Route::post('bfl/outpaint',  [WizardApiController::class, 'outpaint']);
    Route::post('bfl/remove-bg', [WizardApiController::class, 'removeBg']);

    Route::post('foto-pratica/upload-temp', [WizardApiController::class, 'uploadTemp']);
    Route::post('foto-pratica/salva-url',   [WizardApiController::class, 'salvaUrl']);

    // Ricordino Designer (dati Memorial).
    Route::get('santi',                [RicordinoApiController::class, 'santiIndex']);
    Route::post('santi',               [RicordinoApiController::class, 'santiStore']);
    Route::get('ricordino-templates',                [RicordinoApiController::class, 'templatesIndex']);
    Route::post('ricordino-templates',               [RicordinoApiController::class, 'templatesStore']);
    Route::put('ricordino-templates/{template}',     [RicordinoApiController::class, 'templatesUpdate']);
    Route::delete('ricordino-templates/{template}',  [RicordinoApiController::class, 'templatesDestroy']);
    Route::post('defunto/{defunto}/ricordino', [RicordinoApiController::class, 'salvaRicordino']);
    Route::post('defunto/{defunto}/gdpr',      [RicordinoApiController::class, 'salvaGdpr']);
});
