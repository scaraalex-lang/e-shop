<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Enums\StatoAgenzia;
use Modules\Commerce\Http\Requests\RegistrazioneAgenziaRequest;
use Modules\Commerce\Models\Agenzia;
use Modules\Commerce\Models\AgenteVendita;

/**
 * Area staff: approvazione manuale delle richieste di account agenzia.
 * Primo pezzo di /gestione.
 */
class GestioneAgenzieController extends Controller
{
    public function index(Request $request): View
    {
        $stato = StatoAgenzia::tryFrom((string) $request->query('stato'));

        $agenzie = Agenzia::query()
            ->with('user')
            ->when($stato, fn ($q) => $q->where('stato', $stato))
            ->orderByRaw("CASE WHEN stato = 'in_attesa' THEN 0 ELSE 1 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('commerce::gestione.agenzie.index', [
            'agenzie' => $agenzie,
            'statoAttivo' => $stato,
            'conteggi' => Agenzia::query()
                ->selectRaw('stato, count(*) as totale')
                ->groupBy('stato')
                ->pluck('totale', 'stato'),
        ]);
    }

    public function create(): View
    {
        return view('commerce::gestione.agenzie.create');
    }

    /**
     * A differenza della registrazione self-service, un'agenzia creata dallo
     * staff nasce già approvata: non ha senso che lo staff approvi se stesso.
     * Stessa transazione e stessa validazione della registrazione pubblica
     * (RegistrazioneAgenziaRequest) — solo l'esito finale cambia.
     */
    public function store(RegistrazioneAgenziaRequest $request): RedirectResponse
    {
        $agenzia = DB::transaction(function () use ($request) {
            $agenzia = Agenzia::create($request->datiAgenzia());

            $user = new User($request->safe()->only(['name', 'email', 'password', 'telefono']));
            $user->ruolo = RuoloUtente::Agenzia;
            $user->agenzia()->associate($agenzia);
            $user->save();

            $agenzia->approva($request->user());

            return $agenzia;
        });

        return redirect()
            ->route('gestione.agenzie.show', $agenzia)
            ->with('stato', "Agenzia {$agenzia->ragione_sociale} creata e già approvata.");
    }

    public function show(Agenzia $agenzia): View
    {
        return view('commerce::gestione.agenzie.show', [
            'agenzia' => $agenzia->load('user', 'agenteVendita'),
            'agenti' => AgenteVendita::orderBy('nome')->get(),
        ]);
    }

    public function assegnaAgente(Request $request, Agenzia $agenzia): RedirectResponse
    {
        $dati = $request->validate([
            'agente_vendita_id' => ['nullable', 'exists:agenti_vendita,id'],
        ]);

        $agenzia->assegnaAgente(
            isset($dati['agente_vendita_id']) ? AgenteVendita::find($dati['agente_vendita_id']) : null
        );

        return redirect()
            ->route('gestione.agenzie.show', $agenzia)
            ->with('stato', $agenzia->agenteVendita
                ? "Agente assegnato: {$agenzia->agenteVendita->nome}."
                : 'Agente tolto dall\'agenzia.');
    }

    public function approva(Request $request, Agenzia $agenzia): RedirectResponse
    {
        $dati = $request->validate([
            'note_interne' => ['nullable', 'string', 'max:2000'],
        ]);

        $agenzia->approva($request->user(), $dati['note_interne'] ?? null);

        return redirect()
            ->route('gestione.agenzie.show', $agenzia)
            ->with('stato', "Agenzia {$agenzia->ragione_sociale} approvata.");
    }

    public function rifiuta(Request $request, Agenzia $agenzia): RedirectResponse
    {
        $dati = $request->validate([
            'motivo_rifiuto' => ['required', 'string', 'max:1000'],
            'note_interne' => ['nullable', 'string', 'max:2000'],
        ]);

        $agenzia->rifiuta($request->user(), $dati['motivo_rifiuto'], $dati['note_interne'] ?? null);

        return redirect()
            ->route('gestione.agenzie.show', $agenzia)
            ->with('stato', "Richiesta di {$agenzia->ragione_sociale} non approvata.");
    }

    public function sospendi(Request $request, Agenzia $agenzia): RedirectResponse
    {
        $dati = $request->validate([
            'note_interne' => ['nullable', 'string', 'max:2000'],
        ]);

        $agenzia->sospendi($request->user(), $dati['note_interne'] ?? null);

        return redirect()
            ->route('gestione.agenzie.show', $agenzia)
            ->with('stato', "Agenzia {$agenzia->ragione_sociale} sospesa.");
    }
}
