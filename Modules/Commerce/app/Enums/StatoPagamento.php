<?php

namespace Modules\Commerce\Enums;

enum StatoPagamento: string
{
    case InAttesa = 'in_attesa';
    case Pagato = 'pagato';
    case Fallito = 'fallito';
    case Rimborsato = 'rimborsato';

    public function etichetta(): string
    {
        return match ($this) {
            self::InAttesa => 'Da saldare',
            self::Pagato => 'Pagato',
            self::Fallito => 'Pagamento non riuscito',
            self::Rimborsato => 'Rimborsato',
        };
    }
}
