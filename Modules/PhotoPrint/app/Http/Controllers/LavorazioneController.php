<?php

namespace Modules\PhotoPrint\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Commerce\Enums\StatoOrdine;
use Modules\Commerce\Models\Ordine;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Necrologio;
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
            'ordine' => $ordine->load('servizi.servizioEditor'),
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
            'sesso' => ['nullable', Rule::in(['M', 'F'])],
            'data_nascita' => ['nullable', 'date', 'before:today'],
            'data_morte' => ['nullable', 'date', 'before_or_equal:today', 'after_or_equal:data_nascita'],
            'frase' => ['nullable', 'string', 'max:200'],
            'preghiera' => ['nullable', 'string', 'max:2000'],
            'luogo_partenza' => ['nullable', 'string', Rule::in(Defunto::LUOGHI_PARTENZA)],
            'indirizzo_cerimonia' => ['nullable', 'string', 'max:255'],
            'cerimonia_at' => ['nullable', 'date'],
            'chiesa' => ['nullable', 'string', 'max:150'],
            'indirizzo_chiesa' => ['nullable', 'string', 'max:255'],
            'cimitero' => ['nullable', 'string', 'max:255'],
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
            'sesso' => $dati['sesso'] ?? null,
            'data_nascita' => $dati['data_nascita'] ?? null,
            'data_morte' => $dati['data_morte'] ?? null,
            'frase' => $dati['frase'] ?? null,
            'preghiera' => $dati['preghiera'] ?? null,
            'luogo_partenza' => $dati['luogo_partenza'] ?? null,
            'indirizzo_cerimonia' => $dati['indirizzo_cerimonia'] ?? null,
            'cerimonia_at' => $dati['cerimonia_at'] ?? null,
            'chiesa' => $dati['chiesa'] ?? null,
            'indirizzo_chiesa' => $dati['indirizzo_chiesa'] ?? null,
            'cimitero' => $dati['cimitero'] ?? null,
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

    /**
     * Apre (creandolo se manca) il Necrologio di questa pratica.
     */
    public function apriNecrologio(Request $request, Ordine $ordine): RedirectResponse
    {
        $necrologio = $this->necrologioDiPratica($request, $ordine);

        return redirect()->route('necrologi.designer', $necrologio);
    }

    /**
     * Apre (creandolo se manca) il Manifesto di questa pratica.
     */
    public function apriManifesto(Request $request, Ordine $ordine): RedirectResponse
    {
        $necrologio = $this->necrologioDiPratica($request, $ordine);

        return redirect()->route('necrologi.manifesto', $necrologio);
    }

    /**
     * Trova o crea il necrologio di questa pratica. L'agenzia è quella
     * dell'ordine, non dell'utente che sta operando: è anche lo staff — che
     * non ha un'agenzia propria — a poter lavorare l'ordine per suo conto.
     *
     * Il necrologio è uno strumento dell'agenzia (vedi NecrologiController):
     * un ordine privato, senza agenzia, non ne ha uno.
     */
    private function necrologioDiPratica(Request $request, Ordine $ordine): Necrologio
    {
        $this->soloSuo($request, $ordine);
        abort_unless($ordine->agenzia_id !== null, 404, 'Il necrologio è uno strumento per le agenzie.');

        $defunto = $ordine->defunto_id ? Defunto::find($ordine->defunto_id) : null;
        abort_unless($defunto !== null, 404, 'Prima compila i dati della persona.');
        abort_unless(FotoPratica::principaleDi($ordine->id), 403, 'Carica prima la fotografia.');

        // occasione/numero_anniversario si copiano dall'ordine solo alla
        // creazione (firstOrCreate non tocca un necrologio già esistente):
        // è una fotografia del momento, non un legame permanente — coerente
        // con l'assenza voluta di una FK Necrologio→Ordine. Un ordine vecchio
        // senza il campo lascia il default 'trigesimo' della migration.
        return Necrologio::firstOrCreate(
            ['defunto_id' => $defunto->id, 'agenzia_id' => $ordine->agenzia_id],
            array_filter([
                'percorso' => Necrologio::componiPercorso($defunto),
                'occasione' => $ordine->occasione?->value,
                'numero_anniversario' => $ordine->numero_anniversario,
            ], fn ($v) => $v !== null)
        );
    }

    /**
     * Un ordine si lavora solo se è "suo" (proprio, o della propria agenzia,
     * o si è staff — vedi Ordine::diChi) ed è ancora aperto.
     */
    private function soloSuo(Request $request, Ordine $ordine): void
    {
        abort_unless($ordine->diChi($request->user()), 404);
        abort_unless($ordine->lavorazioneApribile(), 403, 'Questo ordine non è (più) in lavorazione.');
    }
}
