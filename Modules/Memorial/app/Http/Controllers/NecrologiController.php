<?php

namespace Modules\Memorial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Modules\Memorial\Models\Defunto;
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

        $necrologio = Necrologio::create([
            'defunto_id' => $defunto->id,
            'agenzia_id' => $agenzia->id,
            'percorso' => Necrologio::componiPercorso($defunto),
            'trigesimo_at' => $dati['trigesimo_at'] ?? null,
            'trigesimo_luogo' => $dati['trigesimo_luogo'] ?? null,
            'trigesimo_indirizzo' => $dati['trigesimo_indirizzo'] ?? null,
            'testo' => $dati['testo'] ?? null,
            'og_image' => $this->immaginePredefinita($defunto),
        ]);

        $this->allegaManifesto($request, $necrologio);

        return redirect()
            ->route('necrologi.modifica', $necrologio)
            ->with('stato', 'Necrologio creato. Prima di pubblicarlo serve il consenso della famiglia.');
    }

    public function edit(Request $request, Necrologio $necrologio): View
    {
        $this->soloSuo($request, $necrologio);

        return view('memorial::necrologi.form', [
            'necrologio' => $necrologio->load('defunto'),
            'defunti' => $this->defuntiDisponibili($request),
            'agenzia' => $this->agenzia($request),
        ]);
    }

    public function update(Request $request, Necrologio $necrologio): RedirectResponse
    {
        $this->soloSuo($request, $necrologio);
        $dati = $this->valida($request, $necrologio);

        $necrologio->update([
            'trigesimo_at' => $dati['trigesimo_at'] ?? null,
            'trigesimo_luogo' => $dati['trigesimo_luogo'] ?? null,
            'trigesimo_indirizzo' => $dati['trigesimo_indirizzo'] ?? null,
            'testo' => $dati['testo'] ?? null,
        ]);

        $this->allegaManifesto($request, $necrologio);

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
     * Il card designer: disegna e salva come file l'anteprima social.
     *
     * WhatsApp e Facebook non eseguono JavaScript, quindi non basta comporre
     * la card a video: serve un PNG pronto a un indirizzo. La foto di
     * partenza è la stessa usata come anteprima predefinita alla creazione.
     */
    public function designer(Request $request, Necrologio $necrologio): View
    {
        $this->soloSuo($request, $necrologio);
        $necrologio->load('defunto');

        $fotoPrincipale = $this->immaginePredefinita($necrologio->defunto);

        return view('memorial::necrologi.card-designer', [
            'necrologio' => $necrologio,
            'defunto' => $necrologio->defunto,
            'templates' => NecrologioCardTemplate::where('agenzia_id', $this->agenzia($request)->id)->latest()->get(),
            'fotoPrincipale' => $fotoPrincipale ? '/storage/'.ltrim($fotoPrincipale, '/') : null,
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

        $template = NecrologioCardTemplate::create([
            'agenzia_id' => $agenzia->id,
            'nome' => $dati['nome'],
            'path' => $request->file('template')->store('necrologi/card-templates', 'public'),
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

        $dati = $request->validate(['image_data' => ['required', 'string']]);

        if (! preg_match('/^data:image\/png;base64,(.+)$/', $dati['image_data'], $m)) {
            abort(422, 'Immagine non valida.');
        }

        $binario = base64_decode($m[1], true);
        abort_if($binario === false, 422, 'Immagine non valida.');

        $vecchia = $necrologio->og_image;

        $path = 'necrologi/og-image/'.$necrologio->id.'-'.Str::lower(Str::random(8)).'.png';
        Storage::disk('public')->put($path, $binario);

        $necrologio->update(['og_image' => $path]);

        if ($vecchia) {
            Storage::disk('public')->delete($vecchia);
        }

        return response()->json(['success' => true, 'url' => '/storage/'.$path]);
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
            'trigesimo_at' => ['nullable', 'date'],
            'trigesimo_luogo' => ['nullable', 'string', 'max:150'],
            'trigesimo_indirizzo' => ['nullable', 'string', 'max:255'],
            'testo' => ['nullable', 'string', 'max:1500'],
            'manifesto' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:12288'],
        ], [
            'manifesto.mimes' => 'Il manifesto dev\'essere un PDF o un\'immagine.',
            'manifesto.max' => 'Il manifesto supera i 12 MB.',
        ]);
    }

    private function allegaManifesto(Request $request, Necrologio $necrologio): void
    {
        if ($request->hasFile('manifesto')) {
            $necrologio->update([
                'manifesto' => $request->file('manifesto')->store('necrologi/manifesti', 'public'),
            ]);
        }
    }

    /**
     * L'anteprima social di partenza, prima che l'agenzia apra il designer:
     * il ritratto già elaborato è già una card valida su WhatsApp.
     */
    private function immaginePredefinita(Defunto $defunto): ?string
    {
        return $defunto->ricordini()->latest()->first()?->anteprima_fronte;
    }

    private function agenzia(Request $request)
    {
        $agenzia = $request->user()?->agenzia;

        abort_unless($agenzia !== null, 403, 'I necrologi sono uno strumento per le onoranze funebri.');

        return $agenzia;
    }

    private function soloSuo(Request $request, Necrologio $necrologio): void
    {
        abort_unless($necrologio->agenzia_id === $this->agenzia($request)->id, 404);
    }

    private function defuntiDisponibili(Request $request)
    {
        return Defunto::orderByDesc('id')->limit(50)->get();
    }
}
