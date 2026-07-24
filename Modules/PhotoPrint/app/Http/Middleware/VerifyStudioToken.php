<?php

namespace Modules\PhotoPrint\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guard degli endpoint /admin/api/* del Foto Manager (FASE 1).
 *
 * Richiede un token condiviso nell'header X-Studio-Token, confrontato in modo
 * time-safe con quello in config. Blocca l'abuso opportunistico della porta
 * pubblica verso il proxy BFL (scanner, hit dirette all'URL). NON tocca i
 * tenant di memoraiengine: agisce solo sulle route dell'e-shop.
 *
 * FASE 2: rimpiazzabile dall'autenticazione dell'area cliente/staff.
 */
class VerifyStudioToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('photoprint.studio_token', '');
        $provided = (string) $request->header('X-Studio-Token', '');

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            return response()->json(['error' => 'Accesso non autorizzato'], 403);
        }

        return $next($request);
    }
}
