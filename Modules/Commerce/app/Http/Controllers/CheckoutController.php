<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Modules\Commerce\Enums\MetodoPagamento;
use Modules\Commerce\Http\Requests\CheckoutRequest;
use Modules\Commerce\Pagamenti\Pagamento;
use Modules\Commerce\Prezzi\Listino;
use Modules\Commerce\Servizi\CreaOrdine;
use Modules\Commerce\Servizi\GestoreCarrello;

class CheckoutController extends Controller
{
    public function __construct(
        private GestoreCarrello $carrelli,
        private Listino $listino,
        private CreaOrdine $creaOrdine,
        private Pagamento $pagamento,
    ) {}

    public function create(Request $request): View|RedirectResponse
    {
        $carrello = $this->carrelli->corrente();

        if (! $carrello || $carrello->vuoto()) {
            return redirect()->route('carrello');
        }

        $utente = $request->user();
        $agenzia = $utente->eAgenziaApprovata() ? $utente->agenzia : null;

        if (! $carrello->soloCrediti() && ($mancante = $this->pezziMancanti($carrello->pezzi(), $agenzia?->ordineMinimoPezzi() ?? 0))) {
            return redirect()->route('carrello')->with(
                'stato',
                "Per il tuo account servono almeno {$mancante['minimo']} pezzi: ne mancano {$mancante['mancano']}.",
            );
        }

        if ($mancantiCrediti = $this->carrelli->creditiMancanti($carrello, $agenzia)) {
            return redirect()->route('carrello')->with(
                'stato',
                "Servono {$mancantiCrediti['richiesti']} crediti, ne avete {$mancantiCrediti['saldo']}: mancano {$mancantiCrediti['mancano']}.",
            );
        }

        return view('commerce::checkout.create', [
            'conto' => $carrello->conto($this->listino, $utente),
            'carrello' => $carrello,
            'agenzia' => $agenzia,
            'metodi' => MetodoPagamento::disponibiliPer($agenzia !== null),
        ]);
    }

    public function store(CheckoutRequest $request): RedirectResponse
    {
        $carrello = $this->carrelli->corrente();

        if (! $carrello || $carrello->vuoto()) {
            return redirect()->route('carrello');
        }

        $utente = $request->user();
        $agenzia = $utente->eAgenziaApprovata() ? $utente->agenzia : null;

        if (! $carrello->soloCrediti() && $this->pezziMancanti($carrello->pezzi(), $agenzia?->ordineMinimoPezzi() ?? 0)) {
            return redirect()->route('carrello');
        }

        if ($this->carrelli->creditiMancanti($carrello, $agenzia)) {
            return redirect()->route('carrello');
        }

        $metodo = $request->metodo();

        $ordine = $this->creaOrdine->da($carrello, $utente, $request->datiConsegna(), $metodo);

        // Solo la carta passa da un incasso: contrassegno e fattura si saldano
        // dopo, l'ordine parte lo stesso.
        if ($metodo->incassaSubito()) {
            $esito = $this->pagamento->incassa($ordine, ['carta' => $request->validated('carta')]);

            if (! $esito->riuscito) {
                $ordine->segnaPagamentoFallito();

                throw ValidationException::withMessages([
                    'carta' => $esito->messaggio,
                ])->redirectTo(route('ordine', $ordine));
            }

            $ordine->registraPagamento($esito->riferimento);
        }

        $ordine->avvia();

        return redirect()
            ->route('ordine', $ordine)
            ->with('appena_confermato', true);
    }

    /** @return array{minimo:int, mancano:int}|null */
    private function pezziMancanti(int $pezzi, int $minimo): ?array
    {
        if ($minimo <= 0 || $pezzi >= $minimo) {
            return null;
        }

        return ['minimo' => $minimo, 'mancano' => $minimo - $pezzi];
    }
}
