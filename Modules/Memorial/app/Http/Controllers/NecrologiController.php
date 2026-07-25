<?php

namespace Modules\Memorial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Modules\Memorial\Models\Defunto;
use Modules\Memorial\Models\Necrologio;

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
            'necrologio' => new Necrologio,
            'defunti' => $this->defuntiDisponibili($request),
            'agenzia' => $this->agenzia($request),
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
     * L'anteprima social di partenza: il ritratto già elaborato.
     *
     * Quando ci sarà il card designer sarà lui a comporre la card vera; per
     * ora una fotografia è già una card valida su WhatsApp.
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
