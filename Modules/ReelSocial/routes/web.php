<?php

use Illuminate\Support\Facades\Route;
use Modules\ReelSocial\Http\Controllers\DefuntoPubblicazioneController;
use Modules\ReelSocial\Http\Controllers\ReelPubblicoController;

/*
 | Il reel visto da chi apre il link condiviso su FB/IG: nessun account,
 | nessuna scadenza legata all'ordine — stesso posizionamento di
 | video/{video} in TributeVideo e storia/{storia} in SocialStory.
 */
Route::get('reel/{reel}', [ReelPubblicoController::class, 'show'])->name('reel.show');

/*
 | La pagina aggregatore, legata a un defunto reale: mostra Storia Social
 | e Video Memoriale (letti dai rispettivi moduli — questo modulo, a
 | differenza di Memorial/PhotoPrint, PUÒ dipendere da entrambi: è
 | esattamente il suo ruolo, un terzo livello sopra due moduli gemelli
 | che non dipendono l'uno dall'altro) e il bottone "Crea reel" che li
 | concatena. Stesso schema di rotta di account/defunti/{defunto}/... in
 | TributeVideo/SocialStory.
 */
Route::middleware('auth')->prefix('account/defunti')->name('defunti.')->group(function () {
    Route::get('{defunto}/pubblicazione-social', [DefuntoPubblicazioneController::class, 'show'])
        ->name('pubblicazione-social.show');
    Route::post('{defunto}/pubblicazione-social/crea-reel', [DefuntoPubblicazioneController::class, 'creaReel'])
        ->name('pubblicazione-social.crea-reel');
});
