<?php

namespace Modules\PhotoPrint\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chi può usare gli editor (Foto Manager e Ricordino Designer).
 *
 * Sostituisce il token condiviso `X-Studio-Token` della Fase 1, che essendo
 * iniettato nella pagina era leggibile da chiunque aprisse il sorgente:
 * fermava gli scanner, non una persona.
 *
 * Passano lo staff e le agenzie approvate. Un'agenzia con la richiesta ancora
 * in attesa non entra: non potrebbe comunque ordinare quello che produce.
 * Va usato SEMPRE dopo `auth`, che si occupa di chi non ha fatto login.
 */
class AccessoStudio
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->eStaff() || $user->eAgenziaApprovata()),
            403,
            'Gli editor sono riservati allo staff e alle agenzie approvate.',
        );

        return $next($request);
    }
}
