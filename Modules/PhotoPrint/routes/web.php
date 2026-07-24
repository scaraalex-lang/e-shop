<?php

use Illuminate\Support\Facades\Route;
use Modules\PhotoPrint\Http\Controllers\PhotoPrintController;
use Modules\PhotoPrint\Http\Controllers\WizardApiController;
use Modules\PhotoPrint\Http\Controllers\RicordinoApiController;
use Modules\PhotoPrint\Http\Middleware\VerifyStudioToken;

/*
 | FASE 1 — PORTING: route pubbliche per vedere i due editor girare.
 | In seguito andranno protette e collocate nell'area cliente B2C / staff.
 */
Route::get('/studio/foto', [PhotoPrintController::class, 'fotoManager'])->name('studio.foto');
Route::get('/studio/ricordino', [PhotoPrintController::class, 'ricordinoDesigner'])->name('studio.ricordino');

/*
 | Endpoint AI del Foto Manager. I path /admin/api/... rispecchiano quelli
 | attesi dal frontend importato. I bfl/* proxano al microservizio Python vivo.
 | FASE 1: protetti da token condiviso (X-Studio-Token) + rate-limit, così la
 | porta pubblica verso il proxy BFL non è abusabile. In Fase 2 → auth cliente/staff.
 */
Route::prefix('admin/api')->middleware([VerifyStudioToken::class, 'throttle:30,1'])->group(function () {
    Route::post('bfl/enhance',   [WizardApiController::class, 'enhance']);
    Route::post('bfl/outpaint',  [WizardApiController::class, 'outpaint']);
    Route::post('bfl/remove-bg', [WizardApiController::class, 'removeBg']);

    Route::post('foto-pratica/upload-temp', [WizardApiController::class, 'uploadTemp']);
    Route::post('foto-pratica/salva-url',   [WizardApiController::class, 'salvaUrl']);

    // Ricordino Designer (dati Memorial).
    Route::get('santi',                [RicordinoApiController::class, 'santiIndex']);
    Route::post('santi',               [RicordinoApiController::class, 'santiStore']);
    Route::get('ricordino-templates',  [RicordinoApiController::class, 'templatesIndex']);
    Route::post('defunto/{defunto}/ricordino', [RicordinoApiController::class, 'salvaRicordino']);
    Route::post('defunto/{defunto}/gdpr',      [RicordinoApiController::class, 'salvaGdpr']);
});
