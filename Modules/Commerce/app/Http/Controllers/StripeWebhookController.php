<?php

namespace Modules\Commerce\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Commerce\Enums\StatoPagamento;
use Modules\Commerce\Models\Ordine;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

/**
 * Dove Stripe conferma un pagamento avviato con Checkout ospitata (vedi
 * PagamentoStripe). Nessuna sessione, nessun login: è Stripe che chiama,
 * verificato dalla firma — non dal fatto di essere autenticati.
 *
 * Pubblica ed esente da CSRF (vedi bootstrap/app.php): la firma
 * (`Stripe-Signature` + webhook secret) è l'unica cosa che rende affidabile
 * questa chiamata. Senza quella verifica, chiunque potrebbe fingere un
 * pagamento riuscito con un POST costruito a mano e sbloccare un ordine
 * gratis — è il punto di sicurezza più importante di tutta l'integrazione.
 */
class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        try {
            $evento = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature', ''),
                config('commerce.stripe.webhook_secret'),
            );
        } catch (\UnexpectedValueException|SignatureVerificationException) {
            return response('Firma non valida.', 400);
        }

        match ($evento->type) {
            'checkout.session.completed' => $this->confermato($evento->data->object),
            'checkout.session.expired', 'checkout.session.async_payment_failed' => $this->fallito($evento->data->object),
            default => null,
        };

        // 200 anche sugli eventi che non gestiamo: altrimenti Stripe li
        // ritenta all'infinito pensando che la consegna sia fallita.
        return response('ok', 200);
    }

    private function confermato(\Stripe\Checkout\Session $session): void
    {
        $ordine = $this->ordineDellaSessione($session);

        if (! $ordine || $ordine->stato_pagamento === StatoPagamento::Pagato) {
            return;
        }

        $ordine->registraPagamento((string) $session->payment_intent);
        $ordine->avvia();
    }

    private function fallito(\Stripe\Checkout\Session $session): void
    {
        $ordine = $this->ordineDellaSessione($session);

        if (! $ordine || $ordine->stato_pagamento === StatoPagamento::Pagato) {
            return;
        }

        $ordine->segnaPagamentoFallito();
    }

    private function ordineDellaSessione(\Stripe\Checkout\Session $session): ?Ordine
    {
        $ordineId = $session->metadata['ordine_id'] ?? null;

        return $ordineId ? Ordine::find($ordineId) : null;
    }
}
