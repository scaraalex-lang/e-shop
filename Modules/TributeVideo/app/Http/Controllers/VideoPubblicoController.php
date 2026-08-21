<?php

namespace Modules\TributeVideo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Modules\TributeVideo\Models\VideoMemoriale;
use Modules\TributeVideo\Servizi\GeneratoreQrVideo;

/**
 * Il video visto da chi scansiona il QR: nessun account, il link è la
 * credenziale (stesso principio di `BozzaPubblicaController`).
 *
 * Criterio di validità diverso: qui NON si controlla lo stato di un
 * eventuale ordine collegato, solo che il render sia `pronto` — il link è
 * pensato per restare raggiungibile anche a ordine chiuso, essendo
 * destinato a un QR fisico permanente inciso sulla fotoceramica.
 */
class VideoPubblicoController extends Controller
{
    public function show(VideoMemoriale $video)
    {
        abort_unless($video->pronto(), 404);

        return view('tributevideo::pubblico.show', ['video' => $video]);
    }

    public function qr(VideoMemoriale $video): Response
    {
        abort_unless($video->pronto(), 404);

        // 100% locale (GD): niente api.qrserver.com, già segnalato come
        // violazione GDPR altrove nel progetto. Stile editoriale oro-panna
        // con logo MemorAI e punti tondi — vedi GeneratoreQrVideo per il
        // perché dei tre occhi arrotondati ma mai circolari.
        $png = (new GeneratoreQrVideo())->png(route('video.show', $video));

        return response($png, 200)->header('Content-Type', 'image/png');
    }
}
