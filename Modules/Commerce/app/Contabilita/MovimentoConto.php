<?php

namespace Modules\Commerce\Contabilita;

use Illuminate\Support\Carbon;
use Modules\Commerce\Models\Ordine;

/**
 * Una riga dell'estratto conto: un evento con una data precisa, non lo stato
 * attuale di un ordine. Lo stesso ordine può comparire più volte in periodi
 * diversi — fatturato un giorno, saldato un altro — perché è quando è
 * successa la cosa che conta, non l'ordine che l'ha generata. Vedi
 * EstrattoConto.
 */
readonly class MovimentoConto
{
    public function __construct(
        public Carbon $data,
        public string $tipo, // 'fattura_emessa' | 'pagamento' | 'crediti_usati'
        public string $etichetta,
        public Ordine $ordine,
        public ?int $importoDenaro = null, // centesimi
        public ?int $importoCrediti = null,
        public ?string $riferimento = null,
    ) {}
}
