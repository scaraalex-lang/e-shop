<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\PhotoPrint\Models\FotoPratica;
use Modules\PhotoPrint\Servizi\LavorazioneCorrente;

/**
 * La lavorazione fotografica di un ordine, vista dal cliente.
 *
 * È il ponte fra l'ordine (Commerce) e i due editor (PhotoPrint): qui si
 * raccolgono i dati del defunto e il consenso, e da qui si entra nel Foto
 * Manager e nel Designer già puntati su questa pratica.
 */
class LavorazioneController extends Controller
{
    public function __construct(private LavorazioneCorrente $lavorazione) {}

    public function show(Request $request, Ordine $ordine): View|RedirectResponse
    {
        $this->soloSuo($request, $ordine);

        // Da qui in poi gli editor sanno di quale pratica si tratta.
        $this->lavorazione->imposta($ordine);

        $defunto = $ordine->defunto_id ? Defunto::find($ordine->defunto_id) : null;
        $ricordino = $defunto?->ricordini()->latest()->first();

        return view('photoprint::lavorazione.show', [
            'ordine' => $ordine,
            'defunto' => $defunto,
            'ricordino' => $ricordino,
            'foto' => FotoPratica::where('ordine_id', $ordine->id)->latest()->get(),
        ]);
    }

    /**
     * I dati del defunto e il consenso all'uso di immagine e dati.
     *
     * Il consenso lo dà chi sta ordinando: è un familiare, e dichiara la
     * propria parentela. Senza, non si lavora la fotografia.
     */
    public function salvaDefunto(Request $request, Ordine $ordine): RedirectResponse
    {
        $this->soloSuo($request, $ordine);

        $dati = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'cognome' => ['required', 'string', 'max:100'],
            'data_nascita' => ['nullable', 'date', 'before:today'],
            'data_morte' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:data_nascita'],
            'frase' => ['nullable', 'string', 'max:200'],
            'preghiera' => ['nullable', 'string', 'max:2000'],
            'gdpr_parentela' => ['required', 'string', 'max:80'],
            'gdpr_consenso' => ['accepted'],
        ], [
            'gdpr_consenso.accepted' => 'Serve il tuo consenso per lavorare la fotografia.',
            'data_morte.after_or_equal' => 'La data non può essere precedente a quella di nascita.',
        ]);

        $defunto = $ordine->defunto_id
            ? Defunto::findOrFail($ordine->defunto_id)
            : new Defunto;

        $defunto->fill([
            'nome' => $dati['nome'],
            'cognome' => $dati['cognome'],
            'data_nascita' => $dati['data_nascita'] ?? null,
            'data_morte' => $dati['data_morte'] ?? null,
            'frase' => $dati['frase'] ?? null,
            'preghiera' => $dati['preghiera'] ?? null,
            'ordine_id' => $ordine->id,
        ])->save();

        $defunto->autorizzaGdpr(
            $request->user()->name,
            $dati['gdpr_parentela'],
            "Consenso raccolto in fase d'ordine {$ordine->numero}.",
        );

        $ordine->forceFill(['defunto_id' => $defunto->id])->save();

        return redirect()
            ->route('lavorazione', $ordine)
            ->with('stato', 'Dati registrati. Ora puoi caricare la fotografia.');
    }

    /**
     * Il cliente approva la bozza: da qui si va in stampa.
     */
    public function approva(Request $request, Ordine $ordine): RedirectResponse
    {
        $this->soloSuo($request, $ordine);

        $defunto = $ordine->defunto_id ? Defunto::find($ordine->defunto_id) : null;
        $ricordino = $defunto?->ricordini()->latest()->first();

        if (! $ricordino) {
            return redirect()
                ->route('lavorazione', $ordine)
                ->with('stato', 'Prima di approvare bisogna comporre il ricordino nel Designer.');
        }

        $ricordino->forceFill(['stato' => 'approvato'])->save();
        $ordine->passaA(StatoOrdine::InProduzione);
        $this->lavorazione->dimentica();

        return redirect()
            ->route('ordine', $ordine)
            ->with('stato', 'Bozza approvata: il tuo ordine va in produzione.');
    }

    /** Un ordine si lavora solo se è il proprio ed è ancora aperto. */
    private function soloSuo(Request $request, Ordine $ordine): void
    {
        abort_unless($ordine->user_id === $request->user()->id, 404);
        abort_unless($ordine->lavorazioneApribile(), 403, 'Questo ordine non è (più) in lavorazione.');
    }
}
