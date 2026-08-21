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

        $conto = $carrello->conto($this->listino, $utente);

        return view('commerce::checkout.create', [
            'conto' => $conto,
            'carrello' => $carrello,
            'agenzia' => $agenzia,
            'metodi' => MetodoPagamento::disponibiliPer($agenzia !== null),
            'creditiSaldo' => $agenzia?->creditiSaldo() ?? 0,
            // Solo un tetto suggerito in UI: la spedizione non è ancora nota
            // qui (si calcola alla conferma), il cap vero è dentro CreaOrdine.
            'creditiMassimoUsabile' => $agenzia ? min($agenzia->creditiSaldo(), intdiv($conto->totale(), 100)) : 0,
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
        $creditiRichiesti = (int) ($request->validated('crediti_usati') ?? 0);

        $ordine = $this->creaOrdine->da($carrello, $utente, $request->datiConsegna(), $metodo, $creditiRichiesti);

        if ($ordine->valoreInDenaro() === 0) {
            // I crediti dell'agenzia coprono tutto: niente da incassare,
            // indipendentemente dal metodo scelto (che restava lì solo per
            // l'eventuale resto).
            $ordine->registraPagamento("Pagato con {$ordine->crediti_usati} crediti");
        } elseif ($metodo->incassaSubito()) {
            // Solo la carta passa da un incasso: contrassegno e fattura si
            // saldano dopo, l'ordine parte lo stesso. L'importo è quello che
            // resta dopo i crediti (vedi PagamentoStripe).
            $avvio = $this->pagamento->avvia($ordine, ['carta' => $request->validated('carta')]);

            // Driver a redirect (Stripe): l'ordine resta "nuovo, non pagato"
            // finché non arriva la conferma sul webhook — non si avvia qui.
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
