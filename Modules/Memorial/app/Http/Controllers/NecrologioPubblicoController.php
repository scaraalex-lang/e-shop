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
        $onoranza = Agenzia::where('slug', $agenzia)->first();

        abort_unless($onoranza !== null, 404);

        $necrologio = Necrologio::query()
            ->where('agenzia_id', $onoranza->id)
            ->where('percorso', $percorso)
            ->with('defunto')
            ->first();

        abort_unless($necrologio && $necrologio->pubblico(), 404);

        return view('memorial::necrologi.pubblico', [
            'necrologio' => $necrologio,
            'defunto' => $necrologio->defunto,
            'agenzia' => $onoranza,
            'indirizzo' => $necrologio->url($onoranza->slug),
        ]);
    }
}
