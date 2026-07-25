<?php

/*
 | Dashboard operativa (/gestione).
 |
 | FASE 1: accesso con una password condivisa, tenuta in .env. Serve a chi
 | gestisce la vetrina, non ai clienti. Quando Commerce porterà gli account
 | staff/agenzia, questo gate va sostituito dall'auth vera (stesso discorso
 | del token di /studio/*).
 */

return [
    // Nessuna password impostata = dashboard chiusa, non aperta.
    'password' => env('GESTIONE_PASSWORD'),
];
