<?php

namespace Modules\Commerce\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Partita IVA italiana: 11 cifre con cifra di controllo (algoritmo di Luhn
 * nella variante dell'Agenzia delle Entrate).
 *
 * Serve a fermare i refusi in fase di registrazione, non a dire che l'azienda
 * esiste davvero: quello lo verifica lo staff in fase di approvazione.
 */
class PartitaIva implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $piva = preg_replace('/\D/', '', (string) $value);

        if (strlen($piva) !== 11) {
            $fail('La partita IVA deve essere di 11 cifre.');

            return;
        }

        $somma = 0;

        for ($i = 0; $i < 11; $i++) {
            $cifra = (int) $piva[$i];

            // Le posizioni pari (indice dispari) si raddoppiano; se il
            // risultato supera 9 se ne sottrae 9.
            if ($i % 2 === 1) {
                $cifra *= 2;

                if ($cifra > 9) {
                    $cifra -= 9;
                }
            }

            $somma += $cifra;
        }

        if ($somma % 10 !== 0) {
            $fail('La partita IVA non è valida: controlla le cifre.');
        }
    }
}
