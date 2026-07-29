<?php

namespace Modules\Commerce\Enums;

/**
 * Per cosa è un necrologio: cambia solo la dicitura di condivisione (vedi
 * Necrologio) — non determina prezzi né quali servizi sono disponibili.
 * Scelta dall'agenzia sull'ordine (indipendente dai servizi attivati, vedi
 * ServizioEditor), o a mano nel form quando il necrologio nasce senza un
 * ordine dietro.
 */
enum Occasione: string
{
    case Funerale = 'funerale';
    case Trigesimo = 'trigesimo';
    case Anniversario = 'anniversario';

    public function etichetta(): string
    {
        return match ($this) {
            self::Funerale => 'Funerale',
            self::Trigesimo => 'Trigesimo',
            self::Anniversario => 'Anniversario',
        };
    }
}
