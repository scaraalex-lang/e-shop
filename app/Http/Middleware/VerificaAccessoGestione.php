<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate della dashboard operativa: lascia passare solo chi ha superato
 * /gestione/entra in questa sessione. Se la password non è configurata la
 * dashboard resta chiusa (fallire chiudendo, non aprendo).
 */
class VerificaAccessoGestione
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('gestione.password')) {
            abort(403, 'Dashboard non configurata: imposta GESTIONE_PASSWORD nel file .env.');
        }

        if (! $request->session()->get('gestione_accesso')) {
            return redirect()->route('gestione.entra');
        }

        return $next($request);
    }
}
