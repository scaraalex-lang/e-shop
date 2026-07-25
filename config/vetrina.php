<?php

/*
 | Voci di navigazione della vetrina.
 |
 | Stanno qui perché servono in due posti (barra sticky desktop e drawer
 | mobile) e non devono divergere. Quando la dashboard operativa gestirà anche
 | il menu, questa lista diventerà una tabella: la forma dei dati è già quella.
 */

return [
    'voci' => [
        ['etichetta' => 'Trigesimali',    'href' => '/categoria/articoli-trigesimali'],
        ['etichetta' => 'Devozionali',    'href' => '/categoria/devozionali'],
        ['etichetta' => 'Photoceramiche', 'href' => '/categoria/photoceramiche'],
        ['etichetta' => 'Ricordini',      'href' => '/prenota/ricordino'],
        ['etichetta' => 'Stampa foto',    'href' => '#'],
        ['etichetta' => 'QR Memoria',     'href' => '#'],
    ],
];
