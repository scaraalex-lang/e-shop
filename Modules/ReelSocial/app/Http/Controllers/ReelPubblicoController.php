<?php

namespace Modules\ReelSocial\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Memorial\Models\Defunto;
use Modules\ReelSocial\Models\Reel;

/**
 * Il reel visto da chi apre il link condiviso: nessun account, il link è la
 * credenziale — stesso principio di VideoPubblicoController/StoriaPubblicaController.
 *
 * Nessun controllo sullo stato di un eventuale ordine collegato: il link
 * resta permanente, pensato per essere incollato su Facebook/Instagram anche
 * a distanza di tempo dall'acquisto.
 */
class ReelPubblicoController extends Controller
{
    public function show(Reel $reel)
    {
        abort_unless($reel->pronto(), 404);

        return view('reelsocial::pubblico.show', [
            'reel' => $reel,
            'defunto' => $reel->defunto_id ? Defunto::find($reel->defunto_id) : null,
        ]);
    }
}
