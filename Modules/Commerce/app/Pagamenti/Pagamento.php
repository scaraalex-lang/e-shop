<?php

namespace Modules\Commerce\Pagamenti;

use Modules\Commerce\Models\Ordine;

/**
 * L'incasso di un ordine.
 *
 * `avvia()` non promette un esito subito: un driver sincrono (il simulato)
 * lo dà nella stessa chiamata, un driver a redirect (Stripe Checkout) sa
 * solo dove mandare il cliente — la conferma vera arriva più tardi via
 * webhook. Vedi AvvioPagamento.
 */
interface Pagamento
{
    /**
     * @param  array<string, mixed>  $dati  Ciò che il cliente ha compilato
     *                                      (oggi il numero di carta di prova,
     *                                      solo per il driver simulato).
     */
    public function avvia(Ordine $ordine, array $dati = []): AvvioPagamento;
}
