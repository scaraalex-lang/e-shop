<?php

use Illuminate\Support\Facades\Route;
use Modules\Memorial\Http\Controllers\MemorialController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('memorials', MemorialController::class)->names('memorial');
});
