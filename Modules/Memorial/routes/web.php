<?php

use Illuminate\Support\Facades\Route;
use Modules\Memorial\Http\Controllers\MemorialController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('memorials', MemorialController::class)->names('memorial');
});
