<?php

namespace Modules\Memorial\Http\Controllers;

use App\Http\Controllers\Controller;
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
    public function show(string $agenzia, string $percorso): View
    {
        [$necrologio, $onoranza] = $this->risolvi($agenzia, $percorso);

        return view('memorial::necrologi.pubblico', [
            'necrologio' => $necrologio,
            'defunto' => $necrologio->defunto,
            'agenzia' => $onoranza,
            'indirizzo' => $necrologio->url($onoranza->slug),
        ]);
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
        ]);
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
