<?php

namespace Modules\Commerce\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protegge quello che solo un'agenzia approvata può vedere o fare: listino
 * riservato, sconti a scaglioni, ordini B2B. Chi ha la richiesta ancora in
 * attesa viene rimandato all'area account, dove trova lo stato della pratica.
 */
class AgenziaApprovata
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->eAgenziaApprovata()) {
            return redirect()->route('account');
        }

        return $next($request);
    }
}
