<?php

namespace Modules\Commerce\Prezzi;

use Modules\Commerce\Models\ScaglionePrezzo;

/**
 * Il costo di una riga, in centesimi. Sempre interi: `pieno` è quanto costa a
 * chiunque, `scontato` quanto costa a questo account.
 *
 * Senza condizioni riservate i due valori coincidono, così chi legge non deve
 * mai chiedersi quale dei due usare: per pagare è sempre `scontato`.
 */
readonly class Prezzo
{
    public function __construct(
        public int $pieno,
        public int $scontato,
        public ?ScaglionePrezzo $scaglione = null,
    ) {}

    public static function senzaSconto(int $importo): self
    {
        return new self($importo, $importo);
    }

    public function haSconto(): bool
    {
        return $this->scontato < $this->pieno;
    }

    public function risparmio(): int
    {
        return $this->pieno - $this->scontato;
    }
}
