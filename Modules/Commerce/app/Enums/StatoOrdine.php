<?php

namespace Modules\Commerce\Enums;

/**
 * Il ciclo di vita di un ordine.
 *
 * `InLavorazione` esiste solo per gli ordini con articoli da personalizzare:
 * è la fase in cui il cliente carica la foto, la fa elaborare e approva la
 * bozza. Un ordine di soli rosari salta direttamente in produzione.
 */
enum StatoOrdine: string
{
    case Nuovo = 'nuovo';
    case InLavorazione = 'in_lavorazione';
    case InProduzione = 'in_produzione';
    case Spedito = 'spedito';
    case Consegnato = 'consegnato';
    case Annullato = 'annullato';

    public function etichetta(): string
    {
        return match ($this) {
            self::Nuovo => 'Ordine ricevuto',
            self::InLavorazione => 'In lavorazione',
            self::InProduzione => 'In produzione',
            self::Spedito => 'Spedito',
            self::Consegnato => 'Consegnato',
            self::Annullato => 'Annullato',
        };
    }

    /** Cosa sta succedendo, detto al cliente. */
    public function racconto(): string
    {
        return match ($this) {
            self::Nuovo => 'Abbiamo ricevuto il tuo ordine e lo stiamo prendendo in carico.',
            self::InLavorazione => 'Stiamo preparando la fotografia e la bozza da farti approvare.',
            self::InProduzione => 'La bozza è approvata: siamo in stampa e finitura.',
            self::Spedito => 'Il pacco è partito ed è in viaggio.',
            self::Consegnato => 'Consegnato. Grazie di averci scelto.',
            self::Annullato => 'Questo ordine è stato annullato.',
        };
    }

    /** Le tappe che il cliente vede nel tracciamento, in ordine. */
    public static function percorso(bool $conLavorazione): array
    {
        return array_values(array_filter([
            self::Nuovo,
            $conLavorazione ? self::InLavorazione : null,
            self::InProduzione,
            self::Spedito,
            self::Consegnato,
        ]));
    }

    public function concluso(): bool
    {
        return in_array($this, [self::Consegnato, self::Annullato], true);
    }
}
