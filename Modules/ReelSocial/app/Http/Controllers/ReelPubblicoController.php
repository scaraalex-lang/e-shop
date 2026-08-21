<?php

namespace Modules\ReelSocial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\Memorial\Models\Defunto;
use Modules\ReelSocial\Models\Reel;
use Modules\TributeVideo\Servizi\GeneratoreQrVideo;

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

    /**
     * QR del link pubblico, mostrato nella pagina privata (non su un
     * supporto fisico come per il video): serve solo per passare il link
     * dal PC dell'agenzia al telefono da cui poi si posta la Storia — su
     * Instagram non si può fare da browser desktop, solo dall'app.
     * Riusa GeneratoreQrVideo così com'è: è già generico (prende un URL
     * qualsiasi), il nome viene dal primo caso d'uso.
     */
    public function qr(Reel $reel): Response
    {
        abort_unless($reel->pronto(), 404);

        $png = (new GeneratoreQrVideo())->png(route('reel.show', $reel));

        return response($png, 200)->header('Content-Type', 'image/png');
    }
}
