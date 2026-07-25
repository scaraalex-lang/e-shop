<?php

namespace Modules\Memorial\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Modules\Memorial\Models\Defunto;

/**
 * Ingresso al flusso ricordini dalla vetrina.
 *
 * Registra i dati della persona da ricordare (e il consenso di chi li fornisce),
 * poi accompagna al Foto Manager e da lì al Ricordino Designer. È il punto in
 * cui nasce il Defunto: l'entità che in Fase 2 legherà l'ordine al ricordino.
 */
class PrenotazioneRicordinoController extends Controller
{
    public function create(): View
    {
        return view('memorial::prenota-ricordino');
    }

    public function store(Request $request): RedirectResponse
    {
        $dati = $request->validate([
            'nome'         => ['required', 'string', 'max:80'],
            'cognome'      => ['required', 'string', 'max:80'],
            'data_nascita' => ['nullable', 'date', 'before:tomorrow'],
            'data_morte'   => ['nullable', 'date', 'before:tomorrow', 'after_or_equal:data_nascita'],
            'frase'        => ['nullable', 'string', 'max:500'],
            'preghiera'    => ['nullable', 'string', 'max:2000'],

            // Consenso di chi conferisce i dati: senza, non si prosegue.
            'gdpr_consenso'       => ['accepted'],
            'gdpr_autorizzato_da' => ['required', 'string', 'max:120'],
            'gdpr_parentela'      => ['nullable', 'string', 'max:60'],
        ], [
            'gdpr_consenso.accepted'       => 'Per proseguire serve la conferma di essere autorizzato a fornire questi dati.',
            'gdpr_autorizzato_da.required' => 'Indica il nome di chi autorizza.',
            'data_morte.after_or_equal'    => 'La data di mancanza non può precedere quella di nascita.',
        ]);

        $defunto = Defunto::create([
            'nome'         => $dati['nome'],
            'cognome'      => $dati['cognome'],
            'data_nascita' => $dati['data_nascita'] ?? null,
            'data_morte'   => $dati['data_morte'] ?? null,
            'anni'         => $this->anni($dati['data_nascita'] ?? null, $dati['data_morte'] ?? null),
            'frase'        => $dati['frase'] ?? null,
            'preghiera'    => $dati['preghiera'] ?? null,
        ]);

        $defunto->autorizzaGdpr(
            $dati['gdpr_autorizzato_da'],
            $dati['gdpr_parentela'] ?? null,
            'Consenso raccolto dal modulo di prenotazione ricordini.',
        );

        // Passo successivo del flusso: la fotografia.
        return redirect()
            ->route('studio.foto', ['defunto' => $defunto->id])
            ->with('prenotazione_ok', 'Dati registrati. Ora sistemiamo la fotografia di ' . $defunto->nomeCompleto() . '.');
    }

    /** Età alla data di mancanza, quando entrambe le date sono note. */
    private function anni(?string $nascita, ?string $morte): ?int
    {
        if (! $nascita || ! $morte) {
            return null;
        }

        return Carbon::parse($nascita)->diffInYears(Carbon::parse($morte));
    }
}
