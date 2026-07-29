<?php

namespace Modules\Commerce\Enums;

/**
 * Per cosa è un servizio/necrologio: cambia la dicitura di condivisione
 * (vedi Necrologio) e, sull'ordine, guida il consumo crediti tramite
 * `daSku()`. Un solo punto di mappatura SKU→occasione, riusato da
 * `CreaOrdine` e da `Necrologio` (che eredita l'occasione dall'ordine
 * quando c'è, o la riceve a mano dal form quando il necrologio nasce senza
 * un ordine dietro).
 */
enum Occasione: string
{
    case Funerale = 'funerale';
    case Trigesimo = 'trigesimo';
    case Anniversario = 'anniversario';

    public static function daSku(string $sku): ?self
    {
        return match ($sku) {
            'SRV-NECRO-FUNERALE' => self::Funerale,
            'SRV-NECRO-TRIGESIMO' => self::Trigesimo,
            'SRV-NECRO-ANNIVERSARIO' => self::Anniversario,
            default => null,
        };
    }

    public function etichetta(): string
    {
        return match ($this) {
            self::Funerale => 'Funerale',
            self::Trigesimo => 'Trigesimo',
            self::Anniversario => 'Anniversario',
        };
    }
}
