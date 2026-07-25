<?php

namespace Modules\PhotoPrint\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\PhotoPrint\Servizi\LavorazioneCorrente;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chi può usare gli editor (Foto Manager e Ricordino Designer).
 *
 * Sostituisce il token condiviso `X-Studio-Token` della Fase 1, che essendo
 * iniettato nella pagina era leggibile da chiunque aprisse il sorgente:
 * fermava gli scanner, non una persona.
 *
 * Passano tre soggetti, per tre ragioni diverse:
 *  - lo staff, che lavora su qualunque pratica;
 *  - le agenzie approvate, che preparano ricordini di mestiere;
 *  - chiunque abbia un PROPRIO ordine aperto che richiede la lavorazione — ed
 *    è così che il privato che ha comprato un trigesimo arriva alla propria
 *    fotografia, senza per questo vedere il lavoro di altri.
 *
 * Un'agenzia con la richiesta ancora in attesa non entra per il secondo
 * motivo, ma può entrare per il terzo se ha comprato qualcosa.
 * Va usato SEMPRE dopo `auth`, che si occupa di chi non ha fatto login.
 */
class AccessoStudio
{
    public function __construct(private LavorazioneCorrente $lavorazione) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $ammesso = $user && (
            $user->eStaff()
            || $user->eAgenziaApprovata()
            || $this->lavorazione->ordine() !== null
        );

        abort_unless(
            $ammesso,
            403,
            'Gli editor si aprono dalla lavorazione di un tuo ordine, oppure con un account agenzia.',
        );

        return $next($request);
    }
}
