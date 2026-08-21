<?php

use Illuminate\Support\Facades\Route;
use Modules\SocialStory\Http\Controllers\DefuntoStoriaController;
use Modules\SocialStory\Http\Controllers\StoriaPubblicaController;
use Modules\SocialStory\Http\Controllers\TemplateStoriaController;

/*
 | La storia vista da chi apre il link condiviso su FB/IG: nessun account,
 | nessuna scadenza legata all'ordine (vedi StoriaPubblicaController).
 | Pubblica di proposito, fuori da ogni gruppo middleware — stesso
 | posizionamento di video/{video} in TributeVideo.
 */
Route::get('storia/{storia}', [StoriaPubblicaController::class, 'show'])->name('storia-social.show');

/*
 | Il designer, legato a un defunto reale (B2B a crediti + B2C sull'acquisto
 | di un prodotto con has_social_story) — vedi DefuntoStoriaController.
 */
Route::middleware('auth')->prefix('account/defunti')->name('defunti.')->group(function () {
    Route::get('{defunto}/storia-social', [DefuntoStoriaController::class, 'show'])->name('storia-social.show');
});

/*
 | AJAX del designer: salvataggio canvas + template, letti/scritti da chi ha
 | già superato il gate della pagina sopra — stesso posizionamento generico
 | (dietro auth, non solo staff) di admin/api/manifesti in Memorial.
 */
Route::middleware('auth')->prefix('admin/api')->group(function () {
    Route::post('storie-social/{storia}/salva', [DefuntoStoriaController::class, 'salva']);

    Route::get('storia-templates', [TemplateStoriaController::class, 'index']);
    Route::post('storia-templates', [TemplateStoriaController::class, 'store']);
    Route::put('storia-templates/{template}', [TemplateStoriaController::class, 'update']);
    Route::delete('storia-templates/{template}', [TemplateStoriaController::class, 'destroy']);
});
