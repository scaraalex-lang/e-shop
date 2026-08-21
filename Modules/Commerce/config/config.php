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
    | finiscono per 0 vengono rifiutate). "stripe" usa Checkout ospitata
    | (vedi PagamentoStripe) — checkout, ordine e tracciamento restano
    | com'erano, cambia solo questo valore.
    */
    'pagamento' => env('COMMERCE_PAGAMENTO', 'simulato'),

    /*
    |--------------------------------------------------------------------------
    | Stripe
    |--------------------------------------------------------------------------
    | Letto solo quando 'pagamento' vale "stripe". Le chiavi non vanno mai
    | committate: solo in .env, sia in test che (più avanti) in live.
    */
    'stripe' => [
        'secret' => env('STRIPE_SECRET'),
        'publishable' => env('STRIPE_PUBLISHABLE'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],
];
