<?php

namespace Modules\Memorial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use Modules\Commerce\Enums\Occasione;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\MovimentoCredito;
use Modules\Commerce\Models\ServizioEditor;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Manifesto;
use Modules\Memorial\Models\ManifestoTemplate;
use Modules\Memorial\Models\MessaggioCordoglio;
use Modules\Memorial\Models\Necrologio;
use Modules\Memorial\Models\NecrologioCardTemplate;

/**
 * I necrologi visti dall'agenzia che li pubblica.
 *
 * Non passano dall'ordine: un'agenzia fa necrologi tutte le settimane e
 * compra un kit trigesimale ogni tanto. Sono lo strumento, non il prodotto.
 */
class NecrologiController extends Controller
{
    public function index(Request $request): View
    {
        return view('memorial::necrologi.index', [
            'necrologi' => Necrologio::query()
                ->select(['id', 'defunto_id', 'agenzia_id', 'percorso', 'trigesimo_at', 'pubblicazione_consenso', 'pubblicato', 'pubblicato_fino_al', 'created_at'])
                ->where('agenzia_id', $this->agenzia($request)->id)
                ->with('defunto')
                ->latest()
                ->paginate(20),
            'agenzia' => $this->agenzia($request),
        ]);
    }

    public function create(Request $request): View
    {
        return view('memorial::necrologi.form', [
            // Il Ricordino Designer arriva qui con ?defunto_id=&testo= quando
            // non trova ancora un necrologio per quel defunto (vedi
            // esistePerDefunto): precompila invece di far ricopiare a mano.
            'necrologio' => new Necrologio(['testo' => $request->query('testo')]),
            'defunti' => $this->defuntiDisponibili($request),
            'agenzia' => $this->agenzia($request),
            'defuntoPreselezionato' => $request->integer('defunto_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $agenzia = $this->agenzia($request);
        $dati = $this->valida($request);

        $defunto = Defunto::findOrFail($dati['defunto_id']);

        // Terzo passo della catena canalizzata (Foto → Manifesto → Necrologio):
        // il necrologio del funerale non si crea senza almeno un manifesto già
        // composto. Trigesimo/anniversario restano extra, non bloccanti.
        if ($dati['occasione'] === 'funerale' && ! Manifesto::where('defunto_id', $defunto->id)->exists()) {
            return redirect()
                ->route('defunti.show', $defunto)
                ->with('stato', 'Prima crea il manifesto dalla scheda del defunto.');
        }

        $necrologio = Necrologio::create([
            'defunto_id' => $defunto->id,
            'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
            'occasione' => $dati['occasione'],
            'numero_anniversario' => $dati['numero_anniversario'] ?? null,
            'trigesimo_at' => $dati['trigesimo_at'] ?? null,
            'trigesimo_luogo' => $dati['trigesimo_luogo'] ?? null,
            'trigesimo_indirizzo' => $dati['trigesimo_indirizzo'] ?? null,
            'testo' => $dati['testo'] ?? null,
            'og_image' => $this->immaginePredefinita($defunto),
        ]);

        return redirect()
            ->route('necrologi.modifica', $necrologio)
            ->with('stato', 'Necrologio creato. Prima di pubblicarlo serve il consenso della famiglia.');
    }

    public function edit(Request $request, Necrologio $necrologio): View
    {
        $this->soloSuo($request, $necrologio);

        $agenzia = Agenzia::findOrFail($necrologio->agenzia_id);
        // select() ristretta: stesso motivo di DefuntiController::show(),
        // vedi bug-select-star-sort-memory (colonna 'canvas' JSON grande).
        $manifesti = Manifesto::where('defunto_id', $necrologio->defunto_id)
            ->select(['id', 'defunto_id', 'principale', 'web'])
            ->orderByDesc('principale')->get();

        return view('memorial::necrologi.form', [
            'necrologio' => $necrologio->load(['defunto', 'messaggiCordoglio']),
            'defunti' => $this->defuntiDisponibili($request),
            'agenzia' => $this->agenzia($request),
            'servizioEmbed' => ServizioEditor::where('codice', 'embed')->first(),
            'creditiSaldo' => $agenzia->creditiSaldo(),
            'embedTermineMax' => Carbon::now()->addMonths(Necrologio::EMBED_TERMINE_MESI_MASSIMI)->format('Y-m-d'),
            'manifestoPrincipale' => $manifesti->firstWhere('principale', true),
            'manifestiCount' => $manifesti->count(),
        ]);
    }

    /**
     * Una volta pubblicato, il necrologio del funerale si blocca nella
     * struttura: i programmi della cerimonia cambiano spesso e la pagina
     * pubblica deve poterli riflettere in tempo reale, ma testo/card/manifesto
     * non si toccano più — evita che un annuncio già condiviso su WhatsApp
     * cambi sotto agli occhi di chi lo ha già ricevuto.
     */
    public function update(Request $request, Necrologio $necrologio): RedirectResponse
    {
        $this->soloSuo($request, $necrologio);

        if ($necrologio->pubblicato) {
            $dati = $request->validate([
                'trigesimo_at' => ['nullable', 'date'],
                'trigesimo_luogo' => ['nullable', 'string', 'max:150'],
                'trigesimo_indirizzo' => ['nullable', 'string', 'max:255'],
            ]);

            $necrologio->update($dati);

            return redirect()
                ->route('necrologi.modifica', $necrologio)
                ->with('stato', 'Orario e luogo aggiornati.');
        }

        $dati = $this->valida($request, $necrologio);

        $necrologio->update([
            'occasione' => $dati['occasione'],
            'numero_anniversario' => $dati['numero_anniversario'] ?? null,
            'trigesimo_at' => $dati['trigesimo_at'] ?? null,
            'trigesimo_luogo' => $dati['trigesimo_luogo'] ?? null,
            'trigesimo_indirizzo' => $dati['trigesimo_indirizzo'] ?? null,
            'testo' => $dati['testo'] ?? null,
        ]);

        return redirect()
            ->route('necrologi.modifica', $necrologio)
            ->with('stato', 'Modifiche salvate.');
    }

    /**
     * Il consenso alla pubblicazione: distinto da quello raccolto sul
     * defunto per realizzare gli articoli. Chi autorizza si nomina.
     */
    public function consenso(Request $request, Necrologio $necrologio): RedirectResponse
    {
        $this->soloSuo($request, $necrologio);

        $dati = $request->validate([
            'autorizzata_da' => ['required', 'string', 'max:150'],
            'parentela' => ['required', 'string', 'max:80'],
            'conferma' => ['accepted'],
        ], [
            'conferma.accepted' => 'Serve la conferma che il familiare abbia autorizzato la pubblicazione.',
        ]);

        $necrologio->autorizzaPubblicazione($dati['autorizzata_da'], $dati['parentela']);

        return redirect()->route('necrologi.modifica', $necrologio)
            ->with('stato', 'Consenso registrato. Ora puoi pubblicare.');
    }

    public function revoca(Request $request, Necrologio $necrologio): RedirectResponse
    {
        $this->soloSuo($request, $necrologio);
        $necrologio->revocaConsenso();

        return redirect()->route('necrologi.modifica', $necrologio)
            ->with('stato', 'Consenso revocato: la pagina è stata tolta dal pubblico.');
    }

    public function pubblica(Request $request, Necrologio $necrologio): RedirectResponse
    {
        $this->soloSuo($request, $necrologio);

        $dati = $request->validate([
            'fino_al' => ['nullable', 'date', 'after:today'],
        ]);

        $riuscito = $necrologio->pubblica(
            isset($dati['fino_al']) ? Carbon::parse($dati['fino_al']) : null
        );

        return redirect()->route('necrologi.modifica', $necrologio)->with(
            'stato',
            $riuscito
                ? 'Necrologio pubblicato.'
                : 'Senza il consenso della famiglia non si pubblica.',
        );
    }

    public function ritira(Request $request, Necrologio $necrologio): RedirectResponse
    {
        $this->soloSuo($request, $necrologio);
        $necrologio->ritira();

        return redirect()->route('necrologi.modifica', $necrologio)
            ->with('stato', 'Pagina ritirata. Ricorda: quello che è già stato condiviso resta nelle chat.');
    }

    /**
     * L'URL pubblico (social/WhatsApp) è già incluso nel necrologio.
     * Incorporarlo in un iframe sul sito proprio dell'agenzia è un livello a
     * parte, a pagamento — vedi Necrologio::embeddabile(). Due modalità
     * scelte al momento dell'acquisto: "a termine" (scadenza scelta
     * dall'agenzia, non oltre EMBED_TERMINE_MESI_MASSIMI, costo ridotto) o
     * "perpetuo" (scadenza tecnica a EMBED_PERPETUO_ANNI, costo pieno). Un
     * embed "a termine" già scaduto si può ricomprare/rinnovare da qui.
     */
    public function acquistaEmbed(Request $request, Necrologio $necrologio): RedirectResponse
    {
        $this->soloSuo($request, $necrologio);

        if ($necrologio->embeddabile()) {
            return redirect()->route('necrologi.modifica', $necrologio);
        }

        $dati = $request->validate([
            'tipo' => ['required', 'in:termine,perpetuo'],
            'fino_al' => ['required_if:tipo,termine', 'nullable', 'date', 'after:today'],
        ]);

        $limiteTermine = Carbon::now()->addMonths(Necrologio::EMBED_TERMINE_MESI_MASSIMI);
        $aTermine = $dati['tipo'] === 'termine'
            && isset($dati['fino_al'])
            && Carbon::parse($dati['fino_al'])->lessThanOrEqualTo($limiteTermine);

        $agenzia = Agenzia::findOrFail($necrologio->agenzia_id);
        $servizio = ServizioEditor::where('codice', 'embed')->firstOrFail();

        // "A termine" costa meno solo se lo staff ha configurato quel prezzo
        // da /gestione/servizi: senza, non si offre mai un costo nullo.
        $tipoEffettivo = $aTermine && $servizio->costo_crediti_a_termine !== null ? 'termine' : 'perpetuo';
        $scadenza = $tipoEffettivo === 'termine'
            ? Carbon::parse($dati['fino_al'])->startOfDay()
            : Carbon::now()->addYears(Necrologio::EMBED_PERPETUO_ANNI);
        $costo = $tipoEffettivo === 'termine' ? $servizio->costo_crediti_a_termine : $servizio->costo_crediti;

        $esito = DB::transaction(function () use ($agenzia, $costo, $necrologio, $scadenza, $tipoEffettivo) {
            // Lock sui movimenti dell'agenzia: due conferme quasi simultanee
            // non devono poter portare il saldo sotto zero insieme — stesso
            // schema di AttivaServiziOrdine.
            $saldo = (int) $agenzia->movimentiCredito()->lockForUpdate()->sum('quantita');

            if ($saldo < $costo) {
                return false;
            }

            MovimentoCredito::create([
                'agenzia_id' => $agenzia->id,
                'ordine_id' => null,
                'quantita' => -$costo,
                'causale' => sprintf(
                    'Embed %s (fino al %s) per il necrologio di %s',
                    $tipoEffettivo,
                    $scadenza->format('d/m/Y'),
                    $necrologio->defunto?->nomeCompleto() ?? 'un defunto',
                ),
            ]);

            $necrologio->abilitaEmbed($scadenza, $tipoEffettivo);

            return true;
        });

        return redirect()->route('necrologi.modifica', $necrologio)->with(
            'stato',
            $esito
                ? "Embed attivato ({$tipoEffettivo}, fino al {$scadenza->format('d/m/Y')}): trovate il codice da incollare qui sotto."
                : "Servono {$costo} crediti e non ne avete abbastanza.",
        );
    }

    /** Un messaggio di cordoglio fuori luogo: solo l'agenzia del necrologio può toglierlo. */
    public function eliminaMessaggio(Request $request, Necrologio $necrologio, MessaggioCordoglio $messaggio): RedirectResponse
    {
        $this->soloSuo($request, $necrologio);
        abort_unless($messaggio->necrologio_id === $necrologio->id, 404);

        $messaggio->delete();

        return redirect()->route('necrologi.modifica', $necrologio)
            ->with('stato', 'Messaggio eliminato.');
    }

    /**
     * Il card designer: disegna e salva come file l'anteprima social.
     *
     * WhatsApp e Facebook non eseguono JavaScript, quindi non basta comporre
     * la card a video: serve un PNG pronto a un indirizzo. La foto di
     * partenza è la stessa usata come anteprima predefinita alla creazione.
     */
    public function designer(Request $request, Necrologio $necrologio): View
    {
        $this->soloSuo($request, $necrologio);
        abort_if($necrologio->pubblicato, 403, 'Il necrologio è pubblicato: la card non si modifica più.');
        $necrologio->load('defunto');

        $fotoPrincipale = $this->immaginePredefinita($necrologio->defunto);

        return view('memorial::necrologi.card-designer', [
            'necrologio' => $necrologio,
            'defunto' => $necrologio->defunto,
            'templates' => NecrologioCardTemplate::where('agenzia_id', $necrologio->agenzia_id)->latest()->get(),
            'fotoPrincipale' => $fotoPrincipale ? '/storage/'.ltrim($fotoPrincipale, '/') : null,
            'savedCanvas' => $necrologio->card_canvas,
        ]);
    }

    public function templatesIndex(Request $request): JsonResponse
    {
        $templates = NecrologioCardTemplate::where('agenzia_id', $this->agenzia($request)->id)
            ->latest()
            ->get()
            ->map(fn (NecrologioCardTemplate $t) => ['id' => $t->id, 'nome' => $t->nome, 'url' => $t->url()]);

        return response()->json($templates);
    }

    public function templatesStore(Request $request): JsonResponse
    {
        $agenzia = $this->agenzia($request);

        $dati = $request->validate([
            'nome' => ['required', 'string', 'max:100'],
            'template' => ['required', 'image', 'mimes:png', 'max:8192'],
        ], [
            'template.mimes' => 'Il template dev\'essere un PNG.',
        ]);

        $path = $request->file('template')->store($agenzia->cartellaAssetSociali().'/card-templates', 'public');
        abort_if($path === false, 500, 'Salvataggio del template fallito, riprova.');

        $template = NecrologioCardTemplate::create([
            'agenzia_id' => $agenzia->id,
            'nome' => $dati['nome'],
            'path' => $path,
        ]);

        return response()->json(['success' => true, 'id' => $template->id, 'nome' => $template->nome, 'url' => $template->url()]);
    }

    public function templatesDestroy(Request $request, NecrologioCardTemplate $template): JsonResponse
    {
        abort_unless($template->agenzia_id === $this->agenzia($request)->id, 403);

        Storage::disk('public')->delete($template->path);
        $template->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Salva il PNG composto sul canvas come `og_image` del necrologio.
     *
     * Nome file nuovo a ogni salvataggio (non si riscrive lo stesso path):
     * WhatsApp e Facebook mettono in cache l'anteprima per indirizzo, quindi
     * un file rifatto con lo stesso nome rischia di restare quello vecchio
     * negli occhi di chi ha già aperto il link una volta.
     */
    public function salvaCard(Request $request, Necrologio $necrologio): JsonResponse
    {
        $this->soloSuo($request, $necrologio);
        abort_if($necrologio->pubblicato, 403, 'Il necrologio è pubblicato: la card non si modifica più.');

        $dati = $request->validate([
            'image_data' => ['required', 'string'],
            'canvas' => ['nullable', 'string'],
        ]);

        if (! preg_match('/^data:image\/png;base64,(.+)$/', $dati['image_data'], $m)) {
            abort(422, 'Immagine non valida.');
        }

        $binario = base64_decode($m[1], true);
        abort_if($binario === false, 422, 'Immagine non valida.');

        $canvas = null;
        if ($dati['canvas'] ?? null) {
            $canvas = json_decode($dati['canvas'], true);
            abort_if(! is_array($canvas), 422, 'Il progetto della card non è valido.');
        }

        $vecchia = $necrologio->og_image;
        $agenzia = Agenzia::findOrFail($necrologio->agenzia_id);

        $path = $agenzia->cartellaAssetSociali().'/og-image/'.$necrologio->id.'-'.Str::lower(Str::random(8)).'.png';
        $scritto = Storage::disk('public')->put($path, $binario);
        abort_if($scritto === false, 500, 'Salvataggio della card fallito, riprova.');

        $necrologio->update(['og_image' => $path, 'card_canvas' => $canvas]);

        if ($vecchia) {
            Storage::disk('public')->delete($vecchia);
        }

        return response()->json(['success' => true, 'url' => '/storage/'.$path]);
    }

    public function manifestoTemplatesIndex(Request $request): JsonResponse
    {
        $agenzia = $this->agenzia($request);

        // Niente ORDER BY in SQL: `fronte` può arrivare a qualche MB (uno
        // sfondo personalizzato incluso di proposito) e MySQL può finire la
        // memoria di sort ordinando l'intera riga. Il volume per agenzia è
        // ridotto: si ordina in PHP dopo il fetch.
        $templates = ManifestoTemplate::visibiliPer($agenzia->id)->get()
            ->sortByDesc('created_at')
            ->map(fn (ManifestoTemplate $t) => [
                'id' => $t->id,
                'nome' => $t->nome,
                'formato' => $t->formato,
                'globale' => $t->agenzia_id === null,
                'editabile' => $this->manifestoTemplateAppartieneA($request, $t),
                'anteprima' => $t->anteprimaUrl(),
                'fronte' => $t->fronte,
            ]);

        return response()->json($templates);
    }

    public function manifestoTemplatesStore(Request $request): JsonResponse
    {
        $proprietario = $this->proprietarioTemplate($request);

        $dati = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'formato' => ['required', 'string'],
            'fronte' => ['required', 'string'],
            'anteprima' => ['nullable', 'string'],
        ]);

        $fronte = json_decode($dati['fronte'], true);
        abort_if(! is_array($fronte) || empty($fronte['objects']), 422, 'Il manifesto è vuoto: non c\'è niente da salvare come template.');

        $template = ManifestoTemplate::create([
            'nome' => $dati['nome'],
            'formato' => $dati['formato'],
            'agenzia_id' => $proprietario,
            'fronte' => $fronte,
            'anteprima' => $this->salvaAnteprimaDataUrl($dati['anteprima'] ?? null, $this->cartellaTemplateManifesto($proprietario)),
        ]);

        return response()->json(['success' => true, 'id' => $template->id]);
    }

    public function manifestoTemplatesUpdate(Request $request, ManifestoTemplate $template): JsonResponse
    {
        abort_unless($this->manifestoTemplateAppartieneA($request, $template), 403, 'Questo template non è tuo.');

        $dati = $request->validate([
            'nome' => ['required', 'string', 'max:120'],
            'fronte' => ['required', 'string'],
            'anteprima' => ['nullable', 'string'],
        ]);

        $fronte = json_decode($dati['fronte'], true);
        abort_if(! is_array($fronte) || empty($fronte['objects']), 422, 'Il manifesto è vuoto: non c\'è niente da salvare come template.');

        $anteprima = $this->salvaAnteprimaDataUrl($dati['anteprima'] ?? null, $this->cartellaTemplateManifesto($template->agenzia_id));
        if ($anteprima && $template->anteprima) {
            Storage::disk('public')->delete($template->anteprima);
        }

        $template->update(array_filter([
            'nome' => $dati['nome'],
            'fronte' => $fronte,
            'anteprima' => $anteprima,
        ], fn ($v) => $v !== null));

        return response()->json(['success' => true, 'id' => $template->id]);
    }

    public function manifestoTemplatesDestroy(Request $request, ManifestoTemplate $template): JsonResponse
    {
        abort_unless($this->manifestoTemplateAppartieneA($request, $template), 403, 'Questo template non è tuo.');

        if ($template->anteprima) {
            Storage::disk('public')->delete($template->anteprima);
        }
        $template->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Il Ricordino Designer chiede questo prima di offrire "aggiorna la
     * preghiera nel necrologio": dice solo se QUESTA agenzia ne ha già uno
     * per questo defunto, così il designer sa se aggiornarlo o mandare a
     * crearne uno nuovo.
     */
    public function esistePerDefunto(Request $request, Defunto $defunto): JsonResponse
    {
        $necrologio = Necrologio::where('defunto_id', $defunto->id)
            ->where('agenzia_id', $this->agenzia($request)->id)
            ->first();

        return response()->json(['exists' => $necrologio !== null, 'id' => $necrologio?->id]);
    }

    /**
     * Il testo in corsivo scritto nel ricordino (letto come preghiera) può
     * diventare l'annuncio del necrologio dello stesso defunto — ma il
     * Ricordino Designer chiama questo a ogni bozza salvata, quindi solo se
     * l'agenzia non ha già scritto il proprio: altrimenti un annuncio curato
     * verrebbe sovrascritto in silenzio ogni volta che si sposta una foto.
     *
     * Chi apre il designer non è detto abbia un'agenzia (privato, staff sulla
     * pratica di esempio): per loro non esiste un necrologio da aggiornare,
     * quindi si risponde "niente da fare" invece di un errore — il designer
     * chiama questo endpoint a ogni salvataggio, non deve rompersi per chi
     * i necrologi non li usa.
     */
    public function salvaPreghiera(Request $request): JsonResponse
    {
        $agenzia = $request->user()?->agenzia;
        if (! $agenzia) {
            return response()->json(['success' => false]);
        }

        $dati = $request->validate([
            'pratica_id' => ['required', 'integer', 'exists:defunti,id'],
            'prayer' => ['required', 'string', 'max:1500'],
        ]);

        $necrologio = Necrologio::where('defunto_id', $dati['pratica_id'])
            ->where('agenzia_id', $agenzia->id)
            ->first();

        if (! $necrologio || filled($necrologio->testo)) {
            return response()->json(['success' => false]);
        }

        $necrologio->update(['testo' => $dati['prayer']]);

        return response()->json(['success' => true]);
    }

    // ---- aiuti ------------------------------------------------------------

    private function valida(Request $request, ?Necrologio $necrologio = null): array
    {
        return $request->validate([
            'defunto_id' => [$necrologio ? 'nullable' : 'required', 'integer', 'exists:defunti,id'],
            'occasione' => ['required', Rule::in(array_column(Occasione::cases(), 'value'))],
            'numero_anniversario' => ['required_if:occasione,anniversario', 'nullable', 'integer', 'min:1', 'max:99'],
            'trigesimo_at' => ['nullable', 'date'],
            'trigesimo_luogo' => ['nullable', 'string', 'max:150'],
            'trigesimo_indirizzo' => ['nullable', 'string', 'max:255'],
            'testo' => ['nullable', 'string', 'max:1500'],
        ], [
            'numero_anniversario.required_if' => 'Indica di quale anniversario si tratta.',
        ]);
    }

    /** I template manifesto globali (staff) restano in una cartella condivisa; quelli di un'agenzia vanno nella sua. */
    private function cartellaTemplateManifesto(?int $agenziaId): string
    {
        if ($agenziaId === null) {
            return 'necrologi/manifesto-template';
        }

        return Agenzia::findOrFail($agenziaId)->cartellaAssetSociali().'/manifesto-template';
    }

    /**
     * L'anteprima social di partenza, prima che l'agenzia apra il designer:
     * preferiamo la foto principale scelta nel Foto Manager (stessa foto per
     * ricordino, manifesto e card), col ritratto già elaborato del ricordino
     * come ripiego se quella non c'è ancora.
     */
    private function immaginePredefinita(Defunto $defunto): ?string
    {
        return $defunto->fotoPrincipalePath()
            ?? $defunto->ricordini()->latest()->first()?->anteprima_fronte;
    }

    /** Chi possiede un nuovo template manifesto: staff -> globale, agenzia -> suo. */
    private function proprietarioTemplate(Request $request): ?int
    {
        if ($request->user()?->eStaff()) {
            return null;
        }

        return $this->agenzia($request)->id;
    }

    /** Chi può toccare (modificare/eliminare) un template manifesto già esistente. */
    private function manifestoTemplateAppartieneA(Request $request, ManifestoTemplate $template): bool
    {
        $user = $request->user();

        return $template->agenzia_id === null
            ? (bool) $user?->eStaff()
            : $user?->agenzia?->id === $template->agenzia_id;
    }

    /** Salva un'anteprima base64 nello storage e ritorna il path (o null). */
    private function salvaAnteprimaDataUrl(?string $dataUrl, string $dir): ?string
    {
        if (! $dataUrl || ! str_starts_with($dataUrl, 'data:')) {
            return null;
        }
        $comma = strpos($dataUrl, ',');
        if ($comma === false) {
            return null;
        }
        $binario = base64_decode(substr($dataUrl, $comma + 1), true);
        if ($binario === false) {
            return null;
        }
        $path = trim($dir, '/').'/'.Str::uuid().'.jpg';
        Storage::disk('public')->put($path, $binario);

        return $path;
    }

    private function agenzia(Request $request)
    {
        $agenzia = $request->user()?->agenzia;

        abort_unless($agenzia !== null, 403, 'I necrologi sono uno strumento per le onoranze funebri.');

        return $agenzia;
    }

    /**
     * Chi può toccare questo necrologio: la sua agenzia, o lo staff — che non
     * ha un'agenzia propria ma lavora gli ordini B2B per conto delle agenzie
     * (arriva qui dalla lavorazione dell'ordine, non dal self-service).
     */
    private function soloSuo(Request $request, Necrologio $necrologio): void
    {
        $utente = $request->user();

        if ($utente->eStaff()) {
            return;
        }

        abort_unless($utente->agenzia !== null, 403, 'I necrologi sono uno strumento per le onoranze funebri.');
        abort_unless($necrologio->agenzia_id === $utente->agenzia->id, 404);
    }

    private function defuntiDisponibili(Request $request)
    {
        return Defunto::orderByDesc('id')->limit(50)->get();
    }
}
