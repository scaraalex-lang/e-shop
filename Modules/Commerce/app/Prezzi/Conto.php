<?php

namespace Modules\Commerce\Prezzi;

use Illuminate\Support\Collection;

/**
 * Il conto del carrello: le righe col loro costo, più i totali.
 * Tutti gli importi sono interi, in centesimi.
 */
readonly class Conto
{
    /** @param Collection<int, VoceConto> $voci */
    public function __construct(public Collection $voci) {}

    public function totalePieno(): int
    {
        return (int) $this->voci->sum(fn (VoceConto $v) => $v->prezzo->pieno);
    }

    public function totale(): int
    {
        return (int) $this->voci->sum(fn (VoceConto $v) => $v->prezzo->scontato);
    }

    public function risparmio(): int
    {
        return $this->totalePieno() - $this->totale();
    }

    public function haSconti(): bool
    {
        return $this->risparmio() > 0;
    }
}
