<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'staff' => \Modules\Commerce\Http\Middleware\SoloStaff::class,
            'agenzia.approvata' => \Modules\Commerce\Http\Middleware\AgenziaApprovata::class,
        ]);

        // Stripe chiama il webhook senza il nostro token CSRF: si autentica
        // da sé con la firma verificata in StripeWebhookController.
        $middleware->validateCsrfTokens(except: [
            'webhook/stripe',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Anche gli endpoint degli editor (/admin/api/*) sono chiamati solo via
        // JS: gli errori devono arrivare come JSON. Senza questo, una sessione
        // scaduta risponderebbe con il redirect HTML alla pagina di accesso e
        // il Foto Manager si troverebbe a interpretare una pagina come dati.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*', 'admin/api/*'),
        );
    })->create();
