<?php

namespace Modules\Commerce\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Area /gestione: riservata allo staff MemorAI.
 *
 * Risponde 404 e non 403 di proposito: a chi non è staff l'area non deve
 * nemmeno risultare esistente.
 */
class SoloStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->eStaff(), 404);

        return $next($request);
    }
}
