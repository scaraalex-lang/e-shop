<?php

namespace Modules\Commerce\Tests\Concerns;

use App\Models\User;
use Modules\Commerce\Enums\RuoloUtente;
use Modules\Commerce\Models\Agenzia;

/**
 * I soggetti che servono in quasi tutti i test di Commerce: staff, agenzia,
 * referente. Stavano copiati in tre file diversi.
 */
trait CreaSoggetti
{
    /** Partita IVA con cifra di controllo valida. */
    protected const PIVA_VALIDA = '00743110157';

    protected function staff(): User
    {
        $utente = User::factory()->create();
        $utente->ruolo = RuoloUtente::Staff;
        $utente->save();

        return $utente;
    }

    protected function agenzia(array $attributi = []): Agenzia
    {
        return Agenzia::create(array_merge([
            'ragione_sociale' => 'Onoranze Funebri Bianchi S.r.l.',
            'partita_iva' => self::PIVA_VALIDA,
            'indirizzo' => 'Via Roma 12',
            'cap' => '20121',
            'citta' => 'Milano',
            'provincia' => 'MI',
            'telefono' => '0212345678',
        ], $attributi));
    }

    /**
     * Il referente di un'agenzia. Approvata di default: è la condizione che
     * sblocca sconti, fattura e minimo d'ordine.
     */
    protected function referenteAgenzia(bool $approvata = true, array $attributi = []): User
    {
        $agenzia = $this->agenzia($attributi);

        if ($approvata) {
            $agenzia->approva($this->staff());
        }

        $referente = User::factory()->create();
        $referente->ruolo = RuoloUtente::Agenzia;
        $referente->agenzia()->associate($agenzia);
        $referente->save();

        return $referente->fresh();
    }
}
