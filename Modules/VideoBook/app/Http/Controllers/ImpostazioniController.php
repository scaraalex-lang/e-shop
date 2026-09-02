<?php

namespace Modules\VideoBook\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\VideoBook\Models\ProfiloColore;

/**
 * Pannello di impostazioni del modulo, solo staff: oggi solo il profilo ICC
 * del laboratorio di stampa (vedi ProfiloColore) — il primo passo verso
 * l'allineamento colore delle foto esportate al laboratorio vero, non
 * ancora la conversione stessa (che vive in una fase successiva, quando la
 * pipeline di export smetterà di essere interamente client-side — vedi
 * PdfController). Qui si carica solo il file, così l'impaginatore ha da
 * dove leggerlo quando quel passo arriverà.
 */
class ImpostazioniController extends Controller
{
    private const DISK_DIR = 'videobook/profilo-colore';

    public function index(Request $request): View
    {
        abort_unless($request->user()?->eStaff(), 403);

        return view('videobook::impostazioni', [
            'profilo' => ProfiloColore::with('caricatoDa')->latest()->first(),
        ]);
    }

    public function caricaProfiloColore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->eStaff(), 403);

        // Non "mimes:icc,icm": Symfony non riconosce icc/icm come mimetype
        // noto, la regola li scarterebbe anche se genuini — l'estensione
        // basta, non è un file eseguibile né incluso in nessuna pagina.
        $validated = $request->validate([
            'profilo' => ['required', 'file', 'max:10240'], // 10 MB, un profilo ICC è tipicamente pochi KB-MB
        ]);

        $estensione = strtolower($validated['profilo']->getClientOriginalExtension());
        if (! in_array($estensione, ['icc', 'icm'], true)) {
            return back()->withErrors(['profilo' => 'Il file deve avere estensione .icc o .icm.']);
        }

        // storeAs() e non store(): un ICC non ha un mimetype registrato, il
        // nome generato da store() (che indovina l'estensione dal
        // mimetype rilevato) gli darebbe .bin/.txt invece di .icc/.icm.
        $nomeFile = str()->uuid().'.'.$estensione;
        $path = $validated['profilo']->storeAs(self::DISK_DIR, $nomeFile, 'local');

        ProfiloColore::create([
            'path'           => $path,
            'nome_originale' => $validated['profilo']->getClientOriginalName(),
            'caricato_da'    => $request->user()->id,
        ]);

        return back()->with('successo', 'Profilo colore caricato.');
    }
}
