<?php

namespace Modules\Commerce\Pagamenti;

use Modules\Commerce\Models\Ordine;

/**
 * L'incasso di un ordine.
 *
 * Esiste un'implementazione sola, quella simulata: serve a far girare il
 * flusso senza una banca. Quando arriveranno le chiavi Stripe si aggiunge
 * `PagamentoStripe` e si cambia il binding nel service provider — checkout,
 * ordine e tracciamento non se ne accorgono.
 */
interface Pagamento
{
    /**
     * @param  array<string, mixed>  $dati  Ciò che il cliente ha compilato
     *                                      (oggi il numero di carta di prova).
     */
    public function incassa(Ordine $ordine, array $dati = []): EsitoPagamento;
}
