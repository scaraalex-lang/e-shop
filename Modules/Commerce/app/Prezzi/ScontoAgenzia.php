<?php

namespace Modules\Commerce\Prezzi;

use Modules\Commerce\Prezzi\Concerns\FormattaSconto;
use Modules\Commerce\Prezzi\Contracts\FonteSconto;

/**
 * Lo sconto personale di un'agenzia: una percentuale unica, decisa da chi la
 * segue, che sostituisce gli scaglioni generici sul prodotto (vedi
 * Listino::scontoApplicabile — non si sommano).
 */
readonly class ScontoAgenzia implements FonteSconto
{
    use FormattaSconto;

    public function __construct(public float|string $sconto_percentuale) {}
}
