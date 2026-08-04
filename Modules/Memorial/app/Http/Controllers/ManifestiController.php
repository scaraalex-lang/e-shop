<?php

namespace Modules\Memorial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Manifesto;
use Modules\Memorial\Models\Necrologio;
use Modules\Memorial\Servizi\GeneratoreTestoFunerale;

/**
 * I manifesti collegati a un defunto: secondo passo del percorso canalizzato
 * (Foto → Manifesto → Necrologio) sulla Scheda Defunto. Non appartengono a un
 * necrologio: un defunto può averne più di uno (funerale, partecipazioni,
 * trigesimo...), e lo stesso layout può essere duplicato passando da
 * un'occasione all'altra — vedi Manifesto::duplica().
 */
class ManifestiController extends Controller
{
    public function store(Request $request, Defunto $defunto): RedirectResponse
    {
        $this->soloSuo($request, $defunto);
        $this->assicuraAbilitato($defunto);

        $dati = $request->validate([
            'etichetta' => ['required', 'string', 'max:120'],
            'formato' => ['required', 'string'],
        ]);

        $manifesto = Manifesto::create([
            'defunto_id' => $defunto->id,
            'etichetta' => $dati['etichetta'],
            'formato' => $dati['formato'],
            'principale' => ! Manifesto::where('defunto_id', $defunto->id)->exists(),
        ]);

        return redirect()->route('manifesti.designer', $manifesto);
    }

    /**
     * Alternativa a "Disegna nuovo": un file già pronto (PDF o immagine)
     * caricato a mano, senza passare dal designer — stesso gate del passo 2.
     */
    public function carica(Request $request, Defunto $defunto): RedirectResponse
    {
        $this->soloSuo($request, $defunto);
        $this->assicuraAbilitato($defunto);

        $dati = $request->validate([
            'etichetta' => ['required', 'string', 'max:120'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:12288'],
        ], [
            'file.mimes' => 'Il manifesto dev\'essere un PDF o un\'immagine.',
            'file.max' => 'Il manifesto supera i 12 MB.',
        ]);

        $agenzia = $this->agenziaDi($defunto);
        $cartella = $agenzia?->cartellaAssetSociali() ?? 'necrologi/manifesti-staff';

        Manifesto::create([
            'defunto_id' => $defunto->id,
            'etichetta' => $dati['etichetta'],
            'formato' => 'a3l',
            'pdf' => $request->file('file')->store($cartella.'/manifesti', 'public'),
            'principale' => ! Manifesto::where('defunto_id', $defunto->id)->exists(),
        ]);

        return redirect()->route('defunti.show', $defunto)->with('stato', 'Manifesto caricato.');
    }

    public function designer(Request $request, Manifesto $manifesto): View
    {
        $defunto = $manifesto->defunto;
        $this->soloSuo($request, $defunto);

        $agenzia = $this->agenziaDi($defunto);
        $foto = $defunto->fotoPrincipalePath();

        // Il QR sul manifesto punta al necrologio pubblico, se il defunto ne
        // ha già uno: il manifesto non appartiene a un necrologio specifico,
        // quindi si prende il più recente fra quelli dell'agenzia.
        $necrologio = Necrologio::where('defunto_id', $defunto->id)->latest()->first();

        return view('memorial::manifesti.designer', [
            'manifesto' => $manifesto,
            'defunto' => $defunto,
            'praticaData' => array_merge($defunto->toPraticaData(), [
                'necrologio_url' => ($necrologio && $agenzia) ? $necrologio->url($agenzia->slug) : null,
            ]),
            'agenziaData' => ['name' => $agenzia?->ragione_sociale ?? 'MemorAI'],
            'savedCanvas' => $manifesto->canvas,
            'savedFormato' => $manifesto->formato,
            'fotoPrincipale' => $foto ? '/storage/'.ltrim($foto, '/') : null,
        ]);
    }

    /**
     * Salva canvas, PDF di stampa e — automaticamente, ad ogni salvataggio —
     * il JPEG qualità web da mostrare/allegare (oltre alla mini-anteprima che
     * il PDF già produceva prima di questa modifica).
     */
    public function salva(Request $request, Manifesto $manifesto): JsonResponse
    {
        $this->soloSuo($request, $manifesto->defunto);

        $dati = $request->validate([
            'canvas' => ['required', 'string'],
            'formato' => ['required', 'string'],
            'pdf' => ['nullable', 'string'],
            'web' => ['nullable', 'string'],
        ]);

        $canvas = json_decode($dati['canvas'], true);
        abort_if(! is_array($canvas), 422, 'Il manifesto non è valido.');

        $agenzia = $this->agenziaDi($manifesto->defunto);
        $cartella = $agenzia?->cartellaAssetSociali() ?? 'necrologi/manifesti-staff';

        $vecchioPdf = $manifesto->pdf;
        $pathPdf = $vecchioPdf;
        if ($dati['pdf'] ?? null) {
            // jsPDF .output('datauristring') inserisce sempre un "filename=...;"
            // prima di "base64,": va tollerato, non è un formato malformato.
            if (! preg_match('/^data:application\/pdf;(?:filename=[^;]*;)?base64,(.+)$/', $dati['pdf'], $m)) {
                abort(422, 'Il PDF non è valido.');
            }
            $binario = base64_decode($m[1], true);
            abort_if($binario === false, 422, 'Il PDF non è valido.');

            $pathPdf = $cartella.'/manifesti/'.$manifesto->id.'-'.Str::lower(Str::random(8)).'.pdf';
            Storage::disk('public')->put($pathPdf, $binario);
        }

        $vecchioWeb = $manifesto->web;
        $pathWeb = $vecchioWeb;
        if ($dati['web'] ?? null) {
            if (! preg_match('/^data:image\/jpeg;base64,(.+)$/', $dati['web'], $m)) {
                abort(422, 'Il JPEG web non è valido.');
            }
            $binario = base64_decode($m[1], true);
            abort_if($binario === false, 422, 'Il JPEG web non è valido.');

            $pathWeb = $cartella.'/manifesti-web/'.$manifesto->id.'-'.Str::lower(Str::random(8)).'.jpg';
            Storage::disk('public')->put($pathWeb, $binario);
        }

        $manifesto->update([
            'canvas' => $canvas,
            'formato' => $dati['formato'],
            'pdf' => $pathPdf,
            'web' => $pathWeb,
        ]);

        if ($pathPdf !== $vecchioPdf && $vecchioPdf) {
            Storage::disk('public')->delete($vecchioPdf);
        }
        if ($pathWeb !== $vecchioWeb && $vecchioWeb) {
            Storage::disk('public')->delete($vecchioWeb);
        }

        return response()->json(['success' => true, 'pdf' => $manifesto->pdfUrl(), 'web' => $manifesto->webUrl()]);
    }

    /**
     * Il blocco "Info funerale" nel formato editoriale a 4 righe, composto
     * dai dati già in scheda — vedi GeneratoreTestoFunerale. L'operatore lo
     * riceve come testo pronto da mettere sul canvas, modificabile e
     * rigenerabile a piacere, non salvato automaticamente da qui.
     */
    public function generaTestoFunerale(Request $request, Manifesto $manifesto, GeneratoreTestoFunerale $generatore): JsonResponse
    {
        $this->soloSuo($request, $manifesto->defunto);

        return response()->json(['testo' => $generatore->generaPerDefunto($manifesto->defunto)]);
    }

    /** Clona layout e formato: utile per passare dal manifesto del funerale a quello del trigesimo. */
    public function duplica(Request $request, Manifesto $manifesto): RedirectResponse
    {
        $this->soloSuo($request, $manifesto->defunto);

        $dati = $request->validate(['etichetta' => ['required', 'string', 'max:120']]);

        $nuovo = $manifesto->duplica($dati['etichetta']);

        return redirect()->route('manifesti.designer', $nuovo)
            ->with('stato', 'Manifesto duplicato: modificalo per la nuova occasione.');
    }

    public function impostaPrincipale(Request $request, Manifesto $manifesto): RedirectResponse
    {
        $this->soloSuo($request, $manifesto->defunto);
        $manifesto->rendiPrincipale();

        return redirect()->route('defunti.show', $manifesto->defunto)
            ->with('stato', 'Manifesto impostato come principale.');
    }

    public function destroy(Request $request, Manifesto $manifesto): RedirectResponse
    {
        $this->soloSuo($request, $manifesto->defunto);
        $defunto = $manifesto->defunto;

        if ($manifesto->pdf) {
            Storage::disk('public')->delete($manifesto->pdf);
        }
        if ($manifesto->web) {
            Storage::disk('public')->delete($manifesto->web);
        }
        $manifesto->delete();

        return redirect()->route('defunti.show', $defunto)->with('stato', 'Manifesto eliminato.');
    }

    // ---- aiuti ------------------------------------------------------------

    /**
     * Chi può toccare i manifesti di questo defunto: lo staff, o chi ha
     * almeno un ordine legato a questo defunto di cui è titolare (stesso
     * pattern di `Ordine::diChi()`, applicato a tutti gli ordini del defunto
     * perché più ordini possono riferirsi allo stesso defunto_id).
     */
    private function soloSuo(Request $request, Defunto $defunto): void
    {
        $utente = $request->user();

        if ($utente->eStaff()) {
            return;
        }

        abort_unless(
            Ordine::where('defunto_id', $defunto->id)->get()->contains(fn (Ordine $o) => $o->diChi($utente)),
            404,
        );
    }

    private function agenziaDi(Defunto $defunto): ?Agenzia
    {
        $ordine = $defunto->ordine_id ? Ordine::find($defunto->ordine_id) : null;

        return $ordine?->agenzia_id ? Agenzia::find($ordine->agenzia_id) : null;
    }

    /** Gate del secondo passo: foto pronta + servizio "manifesti" attivato su un ordine del defunto. */
    private function assicuraAbilitato(Defunto $defunto): void
    {
        abort_unless($defunto->fotoPrincipalePath() !== null, 403, 'Carica prima la fotografia.');

        $ordini = Ordine::where('defunto_id', $defunto->id)->with('servizi.servizioEditor')->get();
        $abilitato = $ordini->isNotEmpty() && $ordini->contains(fn (Ordine $o) => $o->designerAbilitato('manifesti'));

        abort_unless($abilitato, 403, 'Il servizio Manifesti non è stato attivato per questo defunto.');
    }
}
