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

    Route::get('{necrologio}/designer', [NecrologiController::class, 'designer'])->name('designer');
    Route::get('{necrologio}/manifesto', [NecrologiController::class, 'manifestoDesigner'])->name('manifesto');
});

/*
|--------------------------------------------------------------------------
| Chiamate JS del card designer
|--------------------------------------------------------------------------
| Sotto /admin/api/ per la stessa ragione degli editor di PhotoPrint: è il
| prefisso che le pagine JSON del sistema (401 in JSON, CSRF verificato)
| riconoscono già in bootstrap/app.php. L'ownership si controlla nel
| controller, come per il resto dei necrologi — niente AccessoStudio qui:
| quel middleware ragiona per pratica/ordine, i necrologi ragionano per
| agenzia.
*/
Route::middleware('auth')->prefix('admin/api')->group(function () {
    Route::get('necrologio-card-templates', [NecrologiController::class, 'templatesIndex']);
    Route::post('necrologio-card-templates', [NecrologiController::class, 'templatesStore']);
    Route::delete('necrologio-card-templates/{template}', [NecrologiController::class, 'templatesDestroy']);
    Route::post('necrologi/{necrologio}/salva-card', [NecrologiController::class, 'salvaCard']);

    // Designer manifesti: condivide /admin/api/santi col Ricordino Designer
    // (rotta già definita in Modules/PhotoPrint), non duplicata qui.
    Route::get('manifesto-templates', [NecrologiController::class, 'manifestoTemplatesIndex']);
    Route::post('manifesto-templates', [NecrologiController::class, 'manifestoTemplatesStore']);
    Route::put('manifesto-templates/{template}', [NecrologiController::class, 'manifestoTemplatesUpdate']);
    Route::delete('manifesto-templates/{template}', [NecrologiController::class, 'manifestoTemplatesDestroy']);
    Route::post('necrologi/{necrologio}/salva-manifesto', [NecrologiController::class, 'salvaManifesto']);

    // Chiamati anche dal Ricordino Designer (Modules/PhotoPrint): lì "pratica"
    // è il defunto (PhotoPrintController::ricordinoDesigner passa
    // $defunto->id come praticaId), non un ordine.
    Route::get('necrologio-pratica/{defunto}', [NecrologiController::class, 'esistePerDefunto']);
    Route::post('salva-preghiera', [NecrologiController::class, 'salvaPreghiera']);
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
Route::get('ricordi/{agenzia}/{percorso}/manifesto', [NecrologioPubblicoController::class, 'manifesto'])
    ->name('necrologio.manifesto.pubblico');
