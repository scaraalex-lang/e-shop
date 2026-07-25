<?php

return [
    'name' => 'PhotoPrint',

    // Il token condiviso `studio_token` non esiste più: gli endpoint
    // /admin/api/* sono protetti dall'autenticazione (vedi AccessoStudio).
    // PHOTOPRINT_STUDIO_TOKEN si può togliere da .env.
];
