<?php

namespace Modules\Commerce\Listeners;

use Illuminate\Auth\Events\Login;
use Modules\Commerce\Servizi\GestoreCarrello;

/**
 * Al momento dell'accesso, il carrello riempito da ospite entra in quello
 * dell'account.
 *
 * È il pezzo che tiene in piedi la scelta "carrello libero, account al momento
 * dell'ordine": senza, chi si registra dal checkout troverebbe il carrello
 * vuoto proprio dopo aver deciso di comprare.
 */
class UnisciCarrelloOspite
{
    public function __construct(private GestoreCarrello $carrelli) {}

    public function handle(Login $evento): void
    {
        $this->carrelli->unisci($evento->user);
    }
}
