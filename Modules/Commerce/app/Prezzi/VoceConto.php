<?php

namespace Modules\Commerce\Prezzi;

use Modules\Commerce\Models\RigaCarrello;

/** Una riga del carrello col suo costo già calcolato. */
readonly class VoceConto
{
    public function __construct(
        public RigaCarrello $riga,
        public Prezzo $prezzo,
    ) {}
}
