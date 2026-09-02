<?php

namespace Modules\VideoBook\Enums;

/**
 * Il ciclo di vita del render del video: dalla coda al video pronto (o
 * all'errore) — stessi quattro stati di
 * [[\Modules\TributeVideo\Enums\StatoVideoMemoriale]], stesso proxy di
 * rendering dietro le quinte.
 */
enum StatoRenderVideo: string
{
    case InCoda = 'coda';
    case InElaborazione = 'elaborazione';
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
