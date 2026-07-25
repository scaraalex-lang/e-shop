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
    public function fotoManager(Request $request)
    {
        // Pratica in lavorazione: arriva dal flusso di prenotazione
        // (/prenota/ricordino) come ?defunto=ID. Senza, si resta sulla demo.
        $defunto = $this->defuntoDaRichiesta($request);

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
            'praticaId'   => $defunto?->id ?? 1,
            'nomePratica' => $defunto?->nomeCompleto() ?? 'Anteprima demo',
            'photos'      => $photos,
            // link al passo successivo, con la pratica al seguito
            'linkRicordino' => $defunto
                ? route('studio.ricordino', ['defunto' => $defunto->id])
                : route('studio.ricordino'),
            // schermi stretti: dirottati sul Designer Smart
            'linkSmart' => $defunto
                ? route('studio.ricordino.smart', ['defunto' => $defunto->id])
                : route('studio.ricordino.smart'),
        ]);
    }

    /**
     * Designer Ricordini (santino fronte/retro).
     *
     * FASE 1 — PORTING: dati mock del defunto per precompilare i blocchi testo.
     * praticaId = null così i pulsanti solo-backend (approvazione, necrologio)
     * restano nascosti. La "Dedica AI" è stata rimossa in questa fase.
     */
    public function ricordinoDesigner(Request $request)
    {
        // Defunto della pratica in lavorazione (?defunto=ID dal Foto Manager o
        // dalla dashboard); in mancanza, il primo a DB come in Fase 1. I dati
        // precompilano i blocchi testo; il consenso GDPR è registrabile in-app.
        $defunto = $this->defuntoDaRichiesta($request)
            ?? \Modules\Memorial\Models\Defunto::query()->firstOrFail();

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
            // ritorno al passo precedente senza perdere la pratica
            'linkFotoManager' => route('studio.foto', ['defunto' => $defunto->id]),
            'linkSmart'       => route('studio.ricordino.smart', ['defunto' => $defunto->id]),

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

    /**
     * Designer Smart: la versione da telefono, per il B2C.
     *
     * Non è un editor ridotto, è un percorso diverso: l'impaginazione la decide
     * la dashboard (template con is_smart_default), i dati anagrafici arrivano
     * dalla pratica, la preghiera si sceglie da un archivio e alla persona
     * restano tre gesti — foto, testi, conferma. Chi ha bisogno di lavorare la
     * fotografia sul serio viene mandato alla web app Kerachrom e torna qui con
     * l'immagine pronta.
     */
    public function ricordinoSmart(Request $request)
    {
        $defunto = $this->defuntoDaRichiesta($request)
            ?? \Modules\Memorial\Models\Defunto::query()->firstOrFail();

        $formati = config('photoprint.formati');
        $formato = $request->query('formato');
        $formato = isset($formati[$formato]) ? $formato : array_key_first($formati);

        $template = \Modules\Memorial\Models\RicordinoTemplate::perSmart($formato);
        abort_if(! $template, 503, 'Nessuna impaginazione disponibile per il formato ' . $formato . '.');

        $scala = (float) config('photoprint.scala');
        [$larghezza, $altezza] = $formati[$formato];

        return view('photoprint::ricordino-smart', [
            'defunto'     => $defunto,
            'praticaId'   => $defunto->id,
            'praticaData' => $defunto->toPraticaData(),
            'formato'     => $formato,
            'canvasW'     => $larghezza * $scala,
            'canvasH'     => $altezza * $scala,
            'template'    => $template,
            'preghiere'   => \Modules\Memorial\Models\Preghiera::attive()->get()
                ->groupBy(fn ($p) => $p->categoria ?: 'Preghiere'),
            'appKerachrom' => config('kerachrom.app_url'),
            'gdpr' => [
                'consenso' => $defunto->gdpr_consenso,
                'defunto'  => $defunto->nomeCompleto(),
            ],
        ]);
    }

    /**
     * Defunto indicato dalla richiesta (?defunto=ID), se esiste.
     * Null quando il parametro manca o punta a una pratica inesistente: gli
     * editor restano visitabili in modalità dimostrativa come in Fase 1.
     */
    private function defuntoDaRichiesta(Request $request): ?\Modules\Memorial\Models\Defunto
    {
        $id = $request->integer('defunto');

        return $id ? \Modules\Memorial\Models\Defunto::find($id) : null;
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
