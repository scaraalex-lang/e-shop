<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autenticazione
|--------------------------------------------------------------------------
| Gli URL sono in italiano (lingua del dominio), i NOMI delle rotte restano
| quelli standard di Laravel/Breeze: il framework li usa internamente
| (redirect di `auth`, `verified`, `password.confirm`, reset password...).
| Non rinominarli.
*/

Route::middleware('guest')->group(function () {
    Route::get('registrati', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('registrati', [RegisteredUserController::class, 'store']);

    Route::get('accedi', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('accedi', [AuthenticatedSessionController::class, 'store']);

    Route::get('password-dimenticata', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('password-dimenticata', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reimposta-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reimposta-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verifica-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verifica-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('verifica-email/invia', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('conferma-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('conferma-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('esci', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
