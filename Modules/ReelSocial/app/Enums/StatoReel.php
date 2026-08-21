<?php

namespace Modules\ReelSocial\Enums;

/**
 * Il ciclo di vita del render: dalla coda al reel pronto (o all'errore) —
 * stesso schema di Modules\TributeVideo\Enums\StatoVideoMemoriale.
 */
enum StatoReel: string
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
