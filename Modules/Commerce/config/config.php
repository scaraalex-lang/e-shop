<?php

return [
    'name' => 'Commerce',

    /*
    |--------------------------------------------------------------------------
    | Minimo d'ordine B2B
    |--------------------------------------------------------------------------
    | Espresso in NUMERO DI PEZZI, non in euro (regola di business). Vale per
    | le agenzie approvate; ogni agenzia può avere la sua soglia in
    | `agenzie.ordine_minimo_pezzi`, che ha la precedenza su questa.
    */
    'ordine_minimo_pezzi' => (int) env('COMMERCE_ORDINE_MINIMO_PEZZI', 20),

    /*
    |--------------------------------------------------------------------------
    | Incasso
    |--------------------------------------------------------------------------
    | "simulato" fa girare il flusso senza una banca (carte finte: quelle che
    | finiscono per 0 vengono rifiutate). Quando arrivano le chiavi Stripe si
    | aggiunge il driver e si cambia questo valore: checkout, ordine e
    | tracciamento restano com'erano.
    */
    'pagamento' => env('COMMERCE_PAGAMENTO', 'simulato'),
];
