<?php

namespace Modules\Commerce\Prezzi\Concerns;

/**
 * Implementa FonteSconto a partire da una proprietà `sconto_percentuale`.
 * Condivisa fra ScaglionePrezzo e ScontoAgenzia per non duplicare i conti.
 */
trait FormattaSconto
{
    public function scontoInCentesimiDiPunto(): int
    {
        return (int) round(((float) $this->sconto_percentuale) * 100);
    }

    public function scontoLeggibile(): string
    {
        return rtrim(rtrim(number_format((float) $this->sconto_percentuale, 2, ',', '.'), '0'), ',').'%';
    }
}
