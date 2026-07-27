<?php

namespace Modules\Commerce\Prezzi\Contracts;

/**
 * Qualunque cosa possa generare uno sconto su una riga: uno scaglione per
 * quantità (ScaglionePrezzo) o la percentuale personale di un'agenzia
 * (ScontoAgenzia). Prezzo::$fonteSconto lavora su questo, non sa quale delle
 * due sia — vedi Listino::scontoApplicabile.
 */
interface FonteSconto
{
    /** Lo sconto in centesimi di punto percentuale (12,50% → 1250): arithmetica intera, mai un float. */
    public function scontoInCentesimiDiPunto(): int;

    /** Etichetta pronta per la vetrina: "12,5%" senza zeri inutili. */
    public function scontoLeggibile(): string;
}
