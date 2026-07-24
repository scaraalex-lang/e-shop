<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PhotoPrintController extends Controller
{
    /**
     * Foto Manager (editor foto).
     *
     * FASE 1 — PORTING: dati mock, nessun backend agganciato ancora.
     * Le foto demo puntano a immagini prodotto reali per far vedere il canvas
     * funzionante; upload/salvataggio/BFL richiederanno il backend (fase 2).
     */
    public function fotoManager()
    {
        $photos = collect([
            // Foto di test reale caricata dall'utente (Sacro Cuore, 1254x1254):
            // apre il canvas Fabric.js su un'immagine vera per la verifica visiva.
            (object) [
                'id'            => 1,
                'url'           => '/storage/photoprint-demo/test-sacro-cuore.jpg',
                'is_principale' => true,
                'tipo'          => 'originale',
            ],
            $this->mockPhoto(2, 'chatgpt-image-3-lug-2026-18-54-50-1.jpg', false, 'originale'),
            $this->mockPhoto(3, 'chatgpt-image-3-lug-2026-18-55-07-2.jpg', false, 'originale'),
        ]);

        return view('photoprint::foto-manager', [
            'praticaId'   => 1,
            'nomePratica' => 'Anteprima demo',
            'photos'      => $photos,
        ]);
    }

    /**
     * Designer Ricordini (santino fronte/retro).
     *
     * FASE 1 — PORTING: dati mock del defunto per precompilare i blocchi testo.
     * praticaId = null così i pulsanti solo-backend (approvazione, necrologio)
     * restano nascosti. La "Dedica AI" è stata rimossa in questa fase.
     */
    public function ricordinoDesigner()
    {
        // Carica un defunto reale dal modulo Memorial (dati che precompilano il
        // ricordino) + eventuale ricordino già salvato. Il consenso GDPR è
        // registrabile in-app dal designer stesso.
        $defunto = \Modules\Memorial\Models\Defunto::query()->firstOrFail();
        $ricordino = $defunto->ricordini()->latest()->first();

        return view('photoprint::ricordino-designer', [
            'praticaId'     => $defunto->id,
            'praticaData'   => $defunto->toPraticaData(),
            'agenziaData'   => ['name' => 'MemorAI'],
            'fotoElaborata' => '/storage/photoprint-demo/test-sacro-cuore.jpg',
            'fotoGalleria'  => [],
            'savedFronte'   => $ricordino?->fronte,
            'savedRetro'    => $ricordino?->retro,
            'savedFormat'   => $ricordino?->formato ?? '7x10',

            // Stato consenso GDPR per il banner/modale del designer.
            'gdpr' => [
                'consenso'       => $defunto->gdpr_consenso,
                'defunto'        => $defunto->nomeCompleto(),
                'autorizzato_da' => $defunto->gdpr_autorizzato_da,
                'parentela'      => $defunto->gdpr_parentela,
                'autorizzato_at' => $defunto->gdpr_autorizzato_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    private function mockPhoto(int $id, string $file, bool $principale, string $tipo): object
    {
        return (object) [
            'id'            => $id,
            'url'           => '/storage/products/' . $file,
            'is_principale' => $principale,
            'tipo'          => $tipo,
        ];
    }
}
