<?php

use Illuminate\Support\Facades\Route;
use Modules\TributeVideo\Http\Controllers\TestController;
use Modules\TributeVideo\Http\Controllers\VideoPubblicoController;

/*
 | Il video visto da chi scansiona il QR: nessun account, nessuna scadenza
 | legata all'ordine (vedi VideoPubblicoController). Pubbliche di proposito,
 | fuori da ogni gruppo middleware — stesso posizionamento di bozza/{revisione}
 | in PhotoPrint.
 */
Route::get('video/{video}', [VideoPubblicoController::class, 'show'])->name('video.show');
Route::get('video/{video}/qr', [VideoPubblicoController::class, 'qr'])->name('video.qr');

/*
 | Pagina di test staff: genera un video vero senza passare da
 | carrello/Stripe, per validare la pipeline end-to-end.
 */
Route::middleware(['auth', 'staff'])->prefix('gestione')->name('gestione.')->group(function () {
    Route::get('video-memoriale', [TestController::class, 'index'])->name('video-memoriale.index');
    Route::post('video-memoriale', [TestController::class, 'store'])->name('video-memoriale.store');
    Route::get('video-memoriale/{videoMemoriale}', [TestController::class, 'show'])->name('video-memoriale.show');
});
