<?php

namespace Modules\Commerce\Pagamenti;

use Modules\Commerce\Models\Ordine;
use Stripe\StripeClient;

/**
 * Incasso vero, via Stripe Checkout ospitata.
 *
 * Non incassa nulla in questa chiamata: crea una Sessione e manda il
 * cliente a pagare sulla pagina di Stripe, che accetta carte di credito,
 * debito e prepagate senza bisogno di distinguerle — è lo stesso circuito.
 * La conferma vera arriva dopo, in modo asincrono, sull'endpoint webhook
 * (vedi StripeWebhookController): qui non si tocca mai lo stato
 * dell'ordine, solo `AvvioPagamento::redirect()`.
 *
 * Managed Payments disattivato esplicitamente: è un'impostazione più recente
 * del Dashboard Stripe (attiva di default sui nuovi account) che calcola lei
 * stessa le tasse e pretende un `tax_code` per riga — inutile qui, perché il
 * totale che mandiamo è già quello finale calcolato dal nostro `Listino`
 * (vedi CreaOrdine). Senza questa riga la Sessione fallisce con "product tax
 * code is missing". Con Managed Payments disattivo, Stripe torna a scegliere
 * i metodi di pagamento da sé in base alle impostazioni del Dashboard —
 * niente `payment_method_types` esplicito, non serve e in alcuni casi Stripe
 * lo rifiuta comunque.
 *
 * Un solo articolo nella Sessione, pari a `valoreInDenaro()`, non a
 * `totale`: le righe, gli sconti e la spedizione sono già calcolati e
 * cristallizzati sull'Ordine (vedi CreaOrdine), non serve ricostruirli lato
 * Stripe — ma se l'agenzia ha coperto una parte in crediti, è solo il resto
 * che Stripe deve incassare (chiamata solo quando il resto è > 0, vedi
 * CheckoutController).
 */
class PagamentoStripe implements Pagamento
{
    public function __construct(private StripeClient $stripe) {}

    public function avvia(Ordine $ordine, array $dati = []): AvvioPagamento
    {
        $session = $this->stripe->checkout->sessions->create([
            'mode' => 'payment',
            'managed_payments' => ['enabled' => false],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => "Ordine {$ordine->numero}",
                    ],
                    'unit_amount' => $ordine->valoreInDenaro(),
                ],
                'quantity' => 1,
            ]],
            // Stessa pagina in entrambi i casi: è "Il conto" a raccontare
            // com'è andata leggendo lo stato_pagamento, aggiornato dal
            // webhook — non un parametro nell'URL di ritorno.
            'success_url' => route('ordine', $ordine),
            'cancel_url' => route('ordine', $ordine),
            'client_reference_id' => (string) $ordine->id,
            'metadata' => ['ordine_id' => $ordine->id],
        ]);

        return AvvioPagamento::redirect($session->url);
    }
}
