<?php

use Illuminate\Support\Facades\Route;
use Modules\PhotoPrint\Http\Controllers\PhotoPrintController;
use Modules\PhotoPrint\Http\Controllers\PraticheController;
use Modules\PhotoPrint\Http\Controllers\WizardApiController;
use Modules\PhotoPrint\Http\Controllers\RicordinoApiController;
use Modules\PhotoPrint\Http\Controllers\LavorazioneController;
use Modules\PhotoPrint\Http\Controllers\ApprovazioneController;
use Modules\PhotoPrint\Http\Controllers\BozzaPubblicaController;
use Modules\PhotoPrint\Http\Middleware\AccessoStudio;

/*
 | La bozza vista dalla famiglia: nessun account, il link E' la credenziale.
 |
 | Pubbliche di proposito. Chi le riceve sta attraversando un lutto:
 | chiedergli di registrarsi per dire "va bene" sarebbe un ostacolo inutile.
 | Il link vale finche' l'ordine e' aperto (controllo nel controller).
 */
Route::get('bozza/{revisione}', [BozzaPubblicaController::class, 'show'])->name('bozza');
Route::post('bozza/{revisione}/approva', [BozzaPubblicaController::class, 'approva'])->name('bozza.approva');
Route::post('bozza/{revisione}/modifiche', [BozzaPubblicaController::class, 'chiediModifiche'])->name('bozza.modifiche');

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

    // L'archivio delle pratiche: sostituisce l'atterraggio automatico sulla
    // pratica di esempio quando si entra negli editor senza un ordine in
    // sessione (vedi PhotoPrintController).
    Route::get('/studio/pratiche', [PraticheController::class, 'index'])->name('pratiche.index');
});

/*
 | La lavorazione fotografica di un ordine, vista dal cliente: dati del
 | defunto, consenso, ingresso negli editor, approvazione della bozza.
 |
 | Basta `auth`: il controllo vero è "questo ordine è tuo ed è aperto", che
 | AccessoStudio non saprebbe fare (non conosce l'ordine finché non si entra
 | di qui). È da questa pagina che gli editor imparano su cosa lavorare.
 */
Route::middleware('auth')->prefix('account/ordini/{ordine}')->group(function () {
    Route::get('lavorazione', [LavorazioneController::class, 'show'])->name('lavorazione');
    Route::post('lavorazione/defunto', [LavorazioneController::class, 'salvaDefunto'])->name('lavorazione.defunto');
    Route::post('lavorazione/approva', [LavorazioneController::class, 'approva'])->name('lavorazione.approva');
    Route::post('lavorazione/necrologio', [LavorazioneController::class, 'apriNecrologio'])->name('lavorazione.necrologio');
    Route::post('lavorazione/manifesto', [LavorazioneController::class, 'apriManifesto'])->name('lavorazione.manifesto');
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
    // Invio della bozza alla famiglia. Sta QUI e non fuori dal prefisso:
    // e' /admin/api/ che il wrapper su window.fetch copre con CSRF e sessione.
    Route::post('ricordino/{ricordino}/invia-approvazione', [ApprovazioneController::class, 'invia']);

    Route::post('bfl/enhance',   [WizardApiController::class, 'enhance']);
    Route::post('bfl/outpaint',  [WizardApiController::class, 'outpaint']);
    Route::post('bfl/remove-bg', [WizardApiController::class, 'removeBg']);

    // Galleria della pratica: caricare, salvare quello che c'è sul canvas,
    // eliminare. Erano chiamate dai due blade ma non erano mai state portate:
    // in Fase 1 la foto di prova era già sul canvas e nessuno premeva "carica".
    Route::post('foto-pratica/upload',      [WizardApiController::class, 'upload']);
    Route::post('foto-pratica/salva',       [WizardApiController::class, 'salva']);
    Route::delete('foto-pratica/{foto}',    [WizardApiController::class, 'elimina'])->whereNumber('foto');

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
