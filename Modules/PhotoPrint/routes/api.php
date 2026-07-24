<?php

use Illuminate\Support\Facades\Route;
use Modules\PhotoPrint\Http\Controllers\PhotoPrintController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('photoprints', PhotoPrintController::class)->names('photoprint');
});
