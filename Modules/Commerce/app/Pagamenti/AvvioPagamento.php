<?php

namespace Modules\Commerce\Pagamenti;

/**
 * Cosa succede quando si avvia l'incasso di un ordine: due forme, non una.
 *
 * Un driver sincrono (il simulato: una carta finta, esito subito) sa già
 * come è andata quando torna da `Pagamento::avvia()`. Un driver a redirect
 * (Stripe Checkout: il cliente esce dal nostro sito per inserire la carta)
 * non sa ancora niente — sa solo dove mandare il cliente. La conferma vera
 * arriva più tardi, fuori da questa chiamata, via webhook.
 *
 * `CheckoutController` distingue i due casi con `richiedeRedirect()` invece
 * di controllare quale classe di driver è attiva: è cosa succede ORA che
 * conta, non quale servizio c'è dietro.
 */
readonly class AvvioPagamento
{
    private function __construct(
        public ?string $redirectUrl,
        public ?EsitoPagamento $esito,
    ) {}

    public static function redirect(string $url): self
    {
        return new self($url, null);
    }

    public static function immediato(EsitoPagamento $esito): self
    {
        return new self(null, $esito);
    }

    public function richiedeRedirect(): bool
    {
        return $this->redirectUrl !== null;
    }
}
