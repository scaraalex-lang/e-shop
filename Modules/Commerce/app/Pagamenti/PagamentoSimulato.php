<?php

namespace Modules\Commerce\Pagamenti;

use Illuminate\Support\Str;
use Modules\Commerce\Models\Ordine;

/**
 * Incasso finto, per provare il flusso senza una banca.
 *
 * Imita la convenzione delle carte di prova dei veri circuiti: una carta che
 * finisce per 0 viene rifiutata. Serve a poter percorrere anche il ramo del
 * pagamento fallito, che è quello che di solito nessuno prova finché non
 * capita a un cliente vero. Sincrono: l'esito si conosce nella stessa
 * chiamata, niente redirect — vedi AvvioPagamento::immediato().
 *
 * NON deve finire in produzione: il service provider lo lega solo quando
 * `commerce.pagamento` vale "simulato".
 */
class PagamentoSimulato implements Pagamento
{
    public function avvia(Ordine $ordine, array $dati = []): AvvioPagamento
    {
        $carta = preg_replace('/\D/', '', (string) ($dati['carta'] ?? ''));

        if (strlen($carta) < 12) {
            return AvvioPagamento::immediato(EsitoPagamento::fallito('Il numero della carta non è completo.'));
        }

        if (str_ends_with($carta, '0')) {
            return AvvioPagamento::immediato(EsitoPagamento::fallito('La carta è stata rifiutata dalla banca. Prova con un\'altra.'));
        }

        return AvvioPagamento::immediato(EsitoPagamento::riuscito('SIM-'.strtoupper(Str::random(12))));
    }
}
