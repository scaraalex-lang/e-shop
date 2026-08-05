<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Http\Requests\AttivaServiziOrdineRequest;
use Modules\Commerce\Models\Ordine;
use Modules\Commerce\Models\ServizioEditor;
use Modules\Commerce\Pagamenti\Pagamento;
use Modules\Commerce\Servizi\AttivaServiziOrdine;
use Modules\Commerce\Servizi\GestoreCarrello;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Ricordino;

/**
 * Gli ordini visti dal cliente: elenco e tracciamento.
 */
class OrdiniController extends Controller
{
    public function index(Request $request): View
    {
        $ordini = Ordine::di($request->user())
            ->with('righe', 'servizi.servizioEditor')
            ->latest()
            ->paginate(10);

        // Anteprima del defunto negli ordini di servizi digitali (Manifesti/
        // Necrologi/Ricordini): stesso dato già mostrato nel dettaglio
        // dell'ordine, qui solo nome+miniatura per riconoscere a colpo
        // d'occhio di chi si tratta senza aprire ogni riga.
        $defunti = Defunto::whereIn('id', $ordini->pluck('defunto_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        $fotoPerDefunto = $defunti->mapWithKeys(function (Defunto $defunto) {
            $path = $defunto->fotoPrincipalePath();

            return [$defunto->id => $path ? '/storage/'.ltrim($path, '/') : null];
        });

        return view('commerce::ordini.index', [
            'ordini' => $ordini,
            'defuntiPerOrdine' => $defunti,
            'fotoPerDefunto' => $fotoPerDefunto,
        ]);
    }

    /**
     * Il punto d'ingresso per cominciare un ordine: prodotto singolo
     * (vetrina) o kit (prodotto composto, oggi solo il kit trigesimo — vedi
     * Product::is_kit/is_componibile). Il percorso a crediti (servizi editor)
     * ha una pagina a sé, vedi servizi() — è l'operatività quotidiana
     * dell'agenzia, merita una voce di menu propria invece di stare
     * nascosto in fondo a "Nuovo ordine".
     *
     * I prodotti singoli sono elencati qui per categoria invece di rimandare
     * alla vetrina pubblica: l'agenzia lavora da dentro il proprio account,
     * non naviga il sito come un cliente B2C (vedi CLAUDE.md/memoria
     * b2b-b2c-separazione-catalogo). Stesso listino e stesso motore prezzi
     * del pubblico: lo sconto B2B si applica in carrello, non qui.
     */
    public function nuovo(Request $request): View
    {
        $categorie = Category::query()
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')])
            ->whereNull('parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Category $categoria) {
                $ids = $categoria->children->pluck('id')->push($categoria->id);

                $categoria->setRelation('prodotti', Product::active()
                    ->whereIn('category_id', $ids)
                    ->where('is_kit', false)
                    ->where('is_componibile', false)
                    ->with('category', 'primaryImage')
                    ->orderBy('sort_order')
                    ->get());

                return $categoria;
            })
            ->filter(fn (Category $categoria) => $categoria->prodotti->isNotEmpty())
            ->values();

        return view('commerce::ordini.nuovo', [
            'categorie' => $categorie,
            'kit' => Product::active()
                ->where(fn ($q) => $q->where('is_kit', true)->orWhere('is_componibile', true))
                ->with('category', 'primaryImage')
                ->orderBy('sort_order')
                ->get(),
        ]);
    }

    /**
     * Servizi a crediti (ricordini/manifesti/necrologi): il percorso da
     * agenzia più usato, prima incastonato dentro "Nuovo ordine".
     */
    public function servizi(Request $request): View
    {
        $agenzia = $request->user()->agenzia;

        return view('commerce::servizi.index', [
            'creditiSaldo' => $agenzia?->creditiSaldo(),
            'prodottoCrediti' => $agenzia ? Product::active()->where('sku', 'SRV-CREDITI-100')->first() : null,
            'servizi' => $agenzia ? ServizioEditor::attivi()->attivabiliSuOrdine()->get() : null,
        ]);
    }

    /**
     * Attiva uno o più servizi editor (ricordini/manifesti/necrologi) su un
     * ordine nuovo, pagati in crediti — vedi AttivaServiziOrdine.
     *
     * Risponde in JSON quando il form la chiama via fetch (progressive
     * enhancement): la pagina Servizi aggiorna il saldo a vista prima di
     * passare alla lavorazione, invece di sparire subito in un redirect.
     */
    public function attivaServizi(AttivaServiziOrdineRequest $request, AttivaServiziOrdine $attiva): RedirectResponse|JsonResponse
    {
        $utente = $request->user();
        $agenzia = $utente->eAgenziaApprovata() ? $utente->agenzia : null;

        abort_unless($agenzia, 404);

        $esito = $attiva->da(
            $utente,
            $agenzia,
            $request->codiciServizio(),
            $request->occasione(),
            $request->validated('numero_anniversario'),
        );

        if (is_array($esito)) {
            if ($request->wantsJson()) {
                return response()->json(['errore' => "Servono {$esito['richiesti']} crediti, ne avete {$esito['saldo']}: mancano {$esito['mancano']}."], 422);
            }

            return redirect()
                ->route('servizi')
                ->with('stato', "Servono {$esito['richiesti']} crediti, ne avete {$esito['saldo']}: mancano {$esito['mancano']}.");
        }

        if ($request->wantsJson()) {
            return response()->json([
                'saldo' => $agenzia->creditiSaldo(),
                'redirect' => route('lavorazione', $esito),
            ]);
        }

        return redirect()->route('lavorazione', $esito);
    }

    public function show(Request $request, Ordine $ordine): View
    {
        // Un ordine si vede se è il proprio, o si è staff, o si è un altro
        // referente della stessa agenzia — vedi Ordine::diChi(). 404 e non
        // 403: l'esistenza dell'ordine di un altro non è un'informazione da
        // dare.
        abort_unless($ordine->diChi($request->user()), 404);

        $defunto = $ordine->defunto_id ? Defunto::find($ordine->defunto_id) : null;
        $fotoPath = $defunto?->fotoPrincipalePath();
        $ricordino = $this->bozzaDi($ordine);

        return view('commerce::ordini.show', [
            'ordine' => $ordine->load('righe.product.primaryImage', 'righe.product.category', 'servizi.servizioEditor'),
            'ricordino' => $ricordino,
            'defunto' => $defunto,
            'fotoUrl' => $fotoPath ? '/storage/'.ltrim($fotoPath, '/') : null,
            'categorieStampa' => $ricordino?->approvato() ? $this->categorieStampa() : collect(),
        ]);
    }

    /**
     * Le categorie che lo staff ha marcato come componibili nell'ordine di
     * stampa (Category::in_ordine_stampa), coi loro prodotti attivi — stesso
     * criterio di composizione di nuovo() ma filtrato alle categorie
     * flaggate invece che a tutte.
     */
    private function categorieStampa(): Collection
    {
        return Category::query()
            ->where('in_ordine_stampa', true)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Category $categoria) {
                $categoria->setRelation('prodotti', Product::active()
                    ->where('category_id', $categoria->id)
                    ->orderBy('sort_order')
                    ->get());

                return $categoria;
            })
            ->filter(fn (Category $categoria) => $categoria->prodotti->isNotEmpty())
            ->values();
    }

    /**
     * Aggiunge al carrello un articolo scelto dalla composizione dell'ordine
     * di stampa, nella pagina di questo ordine — vedi GestoreCarrello::aggiungi().
     *
     * La riga si marca col defunto della pratica: se il carrello ha già
     * articoli di stampa per un defunto diverso, si rifiuta invece di
     * mischiare in spedizione la stampa di due pratiche per errore.
     */
    public function aggiungiStampa(Request $request, Ordine $ordine, GestoreCarrello $carrelli): RedirectResponse
    {
        abort_unless($ordine->diChi($request->user()), 404);

        $dati = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantita' => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $prodotto = Product::active()->findOrFail($dati['product_id']);

        $carrelloAttuale = $carrelli->corrente();
        $defuntoInCarrello = $carrelloAttuale?->righe->pluck('defunto_id')->filter()->unique()->first();

        if ($defuntoInCarrello && $ordine->defunto_id && $defuntoInCarrello !== $ordine->defunto_id) {
            return redirect()
                ->route('ordine', $ordine)
                ->with('stato', 'Nel carrello ci sono già articoli di stampa per un\'altra pratica: completa o svuota quell\'ordine prima di aggiungerne un altro.');
        }

        $carrelli->aggiungi($prodotto, $dati['quantita'], $ordine->defunto_id);

        return redirect()
            ->route('ordine', $ordine)
            ->with('stato', "{$prodotto->name}: aggiunto al carrello.");
    }

    /**
     * Riprende il pagamento di un ordine a carta non riuscito o abbandonato
     * a metà (redirect Stripe mai completato): stesso `Pagamento` del
     * checkout, ma sull'ordine già esistente — niente carrello, niente
     * nuovo ordine, si riusano `totale` e `metodo_pagamento` già salvati.
     */
    public function paga(Request $request, Ordine $ordine, Pagamento $pagamento): RedirectResponse
    {
        abort_unless($ordine->diChi($request->user()), 404);
        abort_unless(
            $ordine->metodo_pagamento === MetodoPagamento::Carta
                && in_array($ordine->stato_pagamento, [StatoPagamento::InAttesa, StatoPagamento::Fallito], true),
            404,
        );

        // 'carta' serve solo al driver simulato (sviluppo): con Stripe il
        // form "Paga ora" non ha alcun campo carta, avvia() lo ignora.
        $avvio = $pagamento->avvia($ordine, ['carta' => $request->input('carta')]);

        if ($avvio->richiedeRedirect()) {
            return redirect()->away($avvio->redirectUrl);
        }

        $esito = $avvio->esito;

        if (! $esito->riuscito) {
            $ordine->segnaPagamentoFallito();

            throw ValidationException::withMessages([
                'carta' => $esito->messaggio,
            ])->redirectTo(route('ordine', $ordine));
        }

        $ordine->registraPagamento($esito->riferimento);
        $ordine->avvia();

        return redirect()
            ->route('ordine', $ordine)
            ->with('stato', 'Pagamento registrato: il tuo ordine va in produzione.');
    }

    /**
     * La bozza del ricordino di questo ordine, se la lavorazione è arrivata
     * a comporla.
     *
     * Commerce legge Memorial e non il contrario: il defunto è il ponte fra
     * ordine e ricordino (vedi CLAUDE.md), e Memorial non conosce gli ordini
     * se non come un id sciolto, quindi non si crea nessun anello.
     */
    private function bozzaDi(Ordine $ordine): ?Ricordino
    {
        if (! $ordine->defunto_id) {
            return null;
        }

        return Ricordino::where('defunto_id', $ordine->defunto_id)->latest()->first();
    }
}
