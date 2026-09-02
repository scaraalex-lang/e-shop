<?php

use Illuminate\Support\Facades\Route;
use Modules\VideoBook\Http\Controllers\EditorController;
use Modules\VideoBook\Http\Controllers\ImpostazioniController;
use Modules\VideoBook\Http\Controllers\PaginaApiController;
use Modules\VideoBook\Http\Controllers\PaginaTemplateController;
use Modules\VideoBook\Http\Controllers\PdfController;
use Modules\VideoBook\Http\Controllers\VideoController;

/*
 | Dall'ordine al libro: crea (la prima volta) o ritrova il progetto legato
 | all'ordine e apre l'editor già puntato su di esso — stesso schema del
 | punto d'ingresso di PhotoPrint (account/ordini/{ordine}/lavorazione), ma
 | qui basta l'id del libro nell'indirizzo: un solo libro per ordine, niente
 | sessione da impostare prima.
 */
Route::middleware('auth')->group(function () {
    Route::get('account/ordini/{ordine}/videobook', [EditorController::class, 'apriDalOrdine'])->name('videobook.apri');
    Route::get('studio/videobook/{libro}', [EditorController::class, 'show'])->name('studio.videobook');

    // Solo staff (controllo dentro ImpostazioniController, stesso criterio di mandaInProduzione()).
    Route::get('admin/videobook/impostazioni', [ImpostazioniController::class, 'index'])->name('videobook.impostazioni');
    Route::post('admin/videobook/impostazioni/profilo-colore', [ImpostazioniController::class, 'caricaProfiloColore'])->name('videobook.impostazioni.profilo-colore');
});

/*
 | Endpoint dell'editor: selettore dei layout (sola lettura) + pagine/foto
 | del libro in lavorazione. Stesso prefisso /admin/api/ di PhotoPrint: nulla
 | da ricordare quando si scrive il fetch() nel blade, il wrapper CSRF/XHR
 | copre già questo path.
 */
Route::prefix('admin/api/videobook')->middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::get('templates', [PaginaTemplateController::class, 'index']);

    Route::put('{libro}/formato', [EditorController::class, 'aggiornaFormato']);
    Route::post('{libro}/produzione', [EditorController::class, 'mandaInProduzione']);
    Route::delete('{libro}/produzione', [EditorController::class, 'riportaInBozza']);

    Route::post('{libro}/pagine/inizializza', [PaginaApiController::class, 'inizializzaPagine']);
    Route::post('{libro}/pagine',           [PaginaApiController::class, 'aggiungiPagina']);
    Route::post('{libro}/pagine/riordina',  [PaginaApiController::class, 'riordinaPagine']);
    Route::put('pagine/{pagina}/template',  [PaginaApiController::class, 'cambiaTemplate']);
    Route::put('pagine/{pagina}/titolo',    [PaginaApiController::class, 'aggiornaTitoloPagina']);
    Route::delete('pagine/{pagina}',        [PaginaApiController::class, 'eliminaPagina']);

    Route::post('pagine/{pagina}/foto', [PaginaApiController::class, 'caricaFoto']);
    Route::put('foto/{foto}',           [PaginaApiController::class, 'aggiornaFoto']);
    Route::put('foto/{foto}/stile',     [PaginaApiController::class, 'aggiornaStileFoto']);
    Route::post('foto/{foto}/auto-correzione', [PaginaApiController::class, 'autoCorreggiFoto']);
    Route::delete('foto/{foto}',        [PaginaApiController::class, 'eliminaFoto']);

    // Box di testo liberi (pannello Strumenti → Box di testo), vedi TestoPagina.
    Route::post('pagine/{pagina}/testi', [PaginaApiController::class, 'aggiungiTesto']);
    Route::put('testi/{testo}',          [PaginaApiController::class, 'aggiornaTesto']);
    Route::put('testi/{testo}/stile',    [PaginaApiController::class, 'aggiornaStileTesto']);
    Route::delete('testi/{testo}',       [PaginaApiController::class, 'eliminaTesto']);

    Route::post('{libro}/video', [VideoController::class, 'genera']);
    Route::get('{libro}/video',  [VideoController::class, 'stato']);

    Route::post('{libro}/pdf', [PdfController::class, 'salva']);
});
