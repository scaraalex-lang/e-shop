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
        // Endpoint proxy/API del Foto Manager: stateless, esclusi dal CSRF.
        // FASE 1 — da proteggere (auth area cliente/staff) in Fase 2.
        $middleware->validateCsrfTokens(except: [
            'admin/api/*',
        ]);

        $middleware->alias([
            'staff' => \Modules\Commerce\Http\Middleware\SoloStaff::class,
            'agenzia.approvata' => \Modules\Commerce\Http\Middleware\AgenziaApprovata::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
