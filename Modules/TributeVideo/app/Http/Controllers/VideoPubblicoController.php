<?php

namespace Modules\TributeVideo\Http\Controllers;

use App\Http\Controllers\Controller;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Response;
use Modules\TributeVideo\Models\VideoMemoriale;

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

        // 100% locale (GD via PngWriter): niente api.qrserver.com, già
        // segnalato come violazione GDPR altrove nel progetto.
        $result = (new Builder(
            writer: new PngWriter(),
            data: route('video.show', $video),
            size: 480,
            margin: 16,
        ))->build();

        return response($result->getString(), 200)
            ->header('Content-Type', $result->getMimeType());
    }
}
