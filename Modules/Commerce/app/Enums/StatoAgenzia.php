<?php

namespace Modules\Commerce\Enums;

/**
 * Ciclo di vita della richiesta di account agenzia.
 *
 * `InAttesa` è lo stato di partenza: l'agenzia può entrare nell'area account
 * e vedere a che punto è la pratica, ma non ha condizioni B2B.
 * `Sospesa` serve a togliere le condizioni a un'agenzia già approvata senza
 * cancellarne lo storico.
 */
enum StatoAgenzia: string
{
    case InAttesa = 'in_attesa';
    case Approvata = 'approvata';
    case Rifiutata = 'rifiutata';
    case Sospesa = 'sospesa';

    public function etichetta(): string
    {
        return match ($this) {
            self::InAttesa => 'In attesa di approvazione',
            self::Approvata => 'Approvata',
            self::Rifiutata => 'Non approvata',
            self::Sospesa => 'Sospesa',
        };
    }

    /**
     * Solo un'agenzia approvata vede listino riservato e sconti a scaglioni.
     */
    public function abilitaCondizioniB2b(): bool
    {
        return $this === self::Approvata;
    }
}
