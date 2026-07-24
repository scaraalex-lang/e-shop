<?php

return [
    'name' => 'PhotoPrint',

    // Token condiviso per gli endpoint /admin/api/* del Foto Manager.
    // FASE 1: protegge la porta pubblica verso il proxy BFL da abuso
    // opportunistico. In Fase 2 sarà sostituito dall'auth area cliente/staff.
    'studio_token' => env('PHOTOPRINT_STUDIO_TOKEN'),
];
