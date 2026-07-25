<?php

return [
    'name' => 'PhotoPrint',

    // Token condiviso per gli endpoint /admin/api/* del Foto Manager.
    // FASE 1: protegge la porta pubblica verso il proxy BFL da abuso
    // opportunistico. In Fase 2 sarà sostituito dall'auth area cliente/staff.
    'studio_token' => env('PHOTOPRINT_STUDIO_TOKEN'),

    // Misure interne del canvas ricordino: millimetri×96dpi × SCALA.
    // Devono restare allineate a FORMATI/SCALA nei blade degli editor e a
    // RicordinoTemplateSeeder, che genera i template in queste coordinate.
    'scala'   => 3.5,
    'formati' => [
        '7x10' => [265, 378],
        '6x9'  => [227, 340],
    ],
];
