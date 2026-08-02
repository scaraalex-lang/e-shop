<?php

namespace Modules\Memorial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\Commerce\Models\Agenzia;
use Modules\Memorial\Models\Necrologio;

/**
 * Il necrologio come lo vede chiunque riceva il link.
 *
 * Pagina pubblica, senza account. Esiste solo se ricorrono tutte e tre le
 * condizioni: il familiare ha autorizzato la pubblicazione, l'agenzia ha
 * acceso l'interruttore, la data di spegnimento non è passata. Se ne manca
 * una: 404, non una pagina che spiega perché — l'esistenza stessa di un
 * necrologio ritirato non è un'informazione da dare.
 */
class NecrologioPubblicoController extends Controller
{
    /**
     * Bloccata dentro un iframe altrui per chi non ha comprato l'embed
     * (`Necrologio::embeddabile()`): l'URL di condivisione resta pubblico e
     * apribile diretto (social/WhatsApp), incorporarla sul sito di qualcun
     * altro è il livello a pagamento — vedi NecrologiController::acquistaEmbed().
     */
    public function show(string $agenzia, string $percorso): Response
    {
        [$necrologio, $onoranza] = $this->risolvi($agenzia, $percorso);

        $response = response()->view('memorial::necrologi.pubblico', [
            'necrologio' => $necrologio,
            'defunto' => $necrologio->defunto,
            'agenzia' => $onoranza,
            'indirizzo' => $necrologio->url($onoranza->slug),
        ]);

        if (! $necrologio->embeddabile()) {
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('Content-Security-Policy', "frame-ancestors 'none'");
        }

        return $response;
    }

    /**
     * Il manifesto condivide interamente il gate del necrologio: stesso
     * consenso, stesso interruttore, stessa scadenza. Non è un'entità con
     * un proprio stato di pubblicazione — è lo stesso evento, un altro foglio.
     */
    public function manifesto(string $agenzia, string $percorso): View
    {
        [$necrologio, $onoranza] = $this->risolvi($agenzia, $percorso);

        abort_unless($necrologio->manifesto !== null, 404);

        return view('memorial::necrologi.manifesto-pubblico', [
            'necrologio' => $necrologio,
            'defunto' => $necrologio->defunto,
            'agenzia' => $onoranza,
            'indirizzo' => $necrologio->urlManifesto($onoranza->slug),
            'messaggi' => $necrologio->messaggiCordoglio,
        ]);
    }

    /**
     * Un pensiero lasciato senza account: solo nome e testo, nessun contatto.
     * Sta sotto il manifesto (il funerale), non sulla card del trigesimo.
     */
    public function inviaMessaggio(Request $request, string $agenzia, string $percorso): RedirectResponse
    {
        [$necrologio] = $this->risolvi($agenzia, $percorso);

        $dati = $request->validate([
            'nome' => ['required', 'string', 'max:80'],
            'messaggio' => ['required', 'string', 'max:1000'],
            // Honeypot: un campo invisibile per chi guarda la pagina, che uno
            // script che compila tutto il form invece riempie.
            'sito_web' => ['prohibited'],
        ]);

        $necrologio->messaggiCordoglio()->create([
            'nome' => $dati['nome'],
            'messaggio' => $dati['messaggio'],
        ]);

        return redirect()
            ->route('necrologio.manifesto.pubblico', ['agenzia' => $agenzia, 'percorso' => $percorso])
            ->with('messaggio_inviato', true);
    }

    /** @return array{0: Necrologio, 1: Agenzia} */
    private function risolvi(string $agenzia, string $percorso): array
    {
        $onoranza = Agenzia::where('slug', $agenzia)->first();

        abort_unless($onoranza !== null, 404);

        $necrologio = Necrologio::query()
            ->where('agenzia_id', $onoranza->id)
            ->where('percorso', $percorso)
            ->with('defunto')
            ->first();

        abort_unless($necrologio && $necrologio->pubblico(), 404);

        return [$necrologio, $onoranza];
    }
}
