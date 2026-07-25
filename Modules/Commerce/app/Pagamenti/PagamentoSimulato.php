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
 * capita a un cliente vero.
 *
 * NON deve finire in produzione: il service provider lo lega solo quando
 * `commerce.pagamento` vale "simulato".
 */
class PagamentoSimulato implements Pagamento
{
    public function incassa(Ordine $ordine, array $dati = []): EsitoPagamento
    {
        $carta = preg_replace('/\D/', '', (string) ($dati['carta'] ?? ''));

        if (strlen($carta) < 12) {
            return EsitoPagamento::fallito('Il numero della carta non è completo.');
        }

        if (str_ends_with($carta, '0')) {
            return EsitoPagamento::fallito('La carta è stata rifiutata dalla banca. Prova con un\'altra.');
        }

        return EsitoPagamento::riuscito('SIM-'.strtoupper(Str::random(12)));
    }
}
