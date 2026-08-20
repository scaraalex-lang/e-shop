<?php

namespace Modules\TributeVideo\Enums;

/**
 * Dove compare la didascalia sopra la foto, nell'overlay del renderer.
 */
enum PosizioneDidascalia: string
{
    case Alto = 'alto';
    case Centro = 'centro';
    case Basso = 'basso';

    public function etichetta(): string
    {
        return match ($this) {
            self::Alto => 'In alto',
            self::Centro => 'Al centro',
            self::Basso => 'In basso',
        };
    }
}
