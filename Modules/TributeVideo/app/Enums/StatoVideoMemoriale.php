<?php

namespace Modules\TributeVideo\Enums;

/**
 * Il ciclo di vita del render: dalla coda al video pronto (o all'errore).
 */
enum StatoVideoMemoriale: string
{
    case InCoda = 'in_coda';
    case InElaborazione = 'in_elaborazione';
    case Pronto = 'pronto';
    case Errore = 'errore';

    public function etichetta(): string
    {
        return match ($this) {
            self::InCoda => 'In coda',
            self::InElaborazione => 'In elaborazione',
            self::Pronto => 'Pronto',
            self::Errore => 'Errore',
        };
    }
}
