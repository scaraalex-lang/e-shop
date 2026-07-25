<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\PhotoPrint\Models\FotoPratica;
use Modules\PhotoPrint\Servizi\LavorazioneCorrente;

/**
 * I due editor.
 *
 * Quando si arriva dalla lavorazione di un ordine, lavorano sui dati veri di
 * quella pratica: le foto caricate dal cliente e il defunto dell'ordine.
 * Staff e agenzie che entrano direttamente trovano ancora la pratica di
 * esempio — l'archivio per account è un passo successivo.
 */
class PhotoPrintController extends Controller
{
    public function __construct(private LavorazioneCorrente $lavorazione) {}

    public function fotoManager()
    {
        if ($ordine = $this->lavorazione->ordine()) {
            $foto = FotoPratica::where('ordine_id', $ordine->id)
                ->orderByDesc('is_principale')
                ->latest()
                ->get()
                ->map(fn (FotoPratica $f) => (object) $f->perGalleria());

            return view('photoprint::foto-manager', [
                'praticaId' => $ordine->id,
                'nomePratica' => "Ordine {$ordine->numero}",
                'photos' => $foto,
                'limiteFotoMb' => $this->limiteFotoMb(),
                'ritorno' => $this->ritorno($ordine),
            ]);
        }

        return view('photoprint::foto-manager', [
            'praticaId' => 1,
            'nomePratica' => 'Anteprima demo',
            'photos' => $this->fotoDiEsempio(),
            'limiteFotoMb' => $this->limiteFotoMb(),
            'ritorno' => $this->ritorno(null),
        ]);
    }

    public function ricordinoDesigner()
    {
        $ordine = $this->lavorazione->ordine();

        $defunto = $ordine?->defunto_id
            ? Defunto::find($ordine->defunto_id)
            : Defunto::query()->first();

        if (! $defunto) {
            // Nessun defunto: si passa dalla lavorazione, che è dove si
            // raccolgono i dati e il consenso.
            return redirect()->route($ordine ? 'lavorazione' : 'account', $ordine);
        }

        $ricordino = $defunto->ricordini()->latest()->first();

        $principale = $ordine
            ? FotoPratica::where('ordine_id', $ordine->id)->where('is_principale', true)->first()
            : null;

        return view('photoprint::ricordino-designer', [
            'praticaId' => $defunto->id,
            'praticaData' => $defunto->toPraticaData(),
            'agenziaData' => ['name' => $ordine?->agenzia?->ragione_sociale ?? 'MemorAI'],
            'fotoElaborata' => $principale?->url() ?? '/storage/photoprint-demo/test-sacro-cuore.jpg',
            'fotoGalleria' => [],
            'savedFronte' => $ricordino?->fronte,
            'savedRetro' => $ricordino?->retro,
            'savedFormat' => $ricordino?->formato ?? '7x10',
            'ritorno' => $this->ritorno($ordine),

            // Stato consenso GDPR per il banner/modale del designer.
            'gdpr' => [
                'consenso' => $defunto->gdpr_consenso,
                'defunto' => $defunto->nomeCompleto(),
                'autorizzato_da' => $defunto->gdpr_autorizzato_da,
                'parentela' => $defunto->gdpr_parentela,
                'autorizzato_at' => $defunto->gdpr_autorizzato_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Da dove si è entrati, e quindi dove si torna uscendo.
     *
     * Gli editor si aprono a schermo pieno e portano fuori dal sito: chi ci
     * arriva dalla lavorazione di un ordine deve poterci rientrare, non
     * ritrovarsi in vetrina con il lavoro a metà. Staff e agenzie, che entrano
     * senza un ordine, tornano al sito come prima.
     *
     * @return array{url: string, etichetta: string}
     */
    private function ritorno(?Ordine $ordine): array
    {
        return $ordine
            ? ['url' => route('lavorazione', $ordine), 'etichetta' => 'Torna all\'ordine']
            : ['url' => url('/'), 'etichetta' => 'Torna al sito'];
    }

    /**
     * Quanti MB accetta davvero il server, per dirlo al browser PRIMA di
     * spedire il file.
     *
     * Senza, una foto troppo grande viene respinta con un 413 secco e
     * l'editor può solo dire "errore di connessione": il cliente non capisce
     * che deve solo ridurre la foto. Si legge dalla configurazione perché è
     * lì che cambia (dev: php.ini della CLI, produzione: pool php-fpm).
     */
    private function limiteFotoMb(): int
    {
        $inMb = function (string $valore): int {
            $numero = (int) $valore;

            return match (strtolower(substr(trim($valore), -1))) {
                'g' => $numero * 1024,
                'm' => $numero,
                'k' => (int) ceil($numero / 1024),
                default => (int) ceil($numero / 1_048_576),
            };
        };

        return max(1, min(
            $inMb((string) ini_get('upload_max_filesize')),
            $inMb((string) ini_get('post_max_size')),
        ));
    }

    /** La pratica di esempio per staff e agenzie che entrano senza un ordine. */
    private function fotoDiEsempio()
    {
        return collect([
            (object) [
                'id' => 1,
                'url' => '/storage/photoprint-demo/test-sacro-cuore.jpg',
                'is_principale' => true,
                'tipo' => 'originale',
            ],
            (object) [
                'id' => 2,
                'url' => '/storage/products/chatgpt-image-3-lug-2026-18-54-50-1.jpg',
                'is_principale' => false,
                'tipo' => 'originale',
            ],
        ]);
    }
}
