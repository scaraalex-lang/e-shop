<?php

/*
 | Web app Kerachrom (disponibile anche su iOS e Android).
 |
 | Il Designer Smart ci manda chi, da telefono, ha bisogno di lavorare la foto
 | sul serio (scontorno, restauro): là si elabora, si scarica l'immagine e la
 | si importa qui per confermare il ricordino.
 |
 | L'indirizzo di default è quello di staging: quando la app va in produzione
 | basta KERACHROM_APP_URL nel .env, senza toccare il codice.
 */

return [
    'app_url' => env('KERACHROM_APP_URL', 'https://staging.d2bhfx46t69ao9.amplifyapp.com/'),
];
