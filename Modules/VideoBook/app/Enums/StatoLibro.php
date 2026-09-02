<?php

namespace Modules\VideoBook\Enums;

/**
 * Il ciclo di vita di un libro: dalla composizione delle pagine alla
 * consegna dei due export (PDF stampa + video). Niente stato di render qui
 * — quello, quando arriverà, vivrà sul singolo export (come `stato` su
 * [[\Modules\TributeVideo\Models\VideoMemoriale]]), non sul libro.
 */
enum StatoLibro: string
{
    case Bozza = 'bozza';
    case Completato = 'completato';

    public function etichetta(): string
    {
        return match ($this) {
            self::Bozza => 'Bozza',
            self::Completato => 'Completato',
        };
    }
}
