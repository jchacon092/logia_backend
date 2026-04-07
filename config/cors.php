<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout'],
    'allowed_methods' => ['*'],

    // Cambia por tu URL de front (Vite por defecto):
    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,

    // Si usas cookies de Sanctum (SPA con sesión), déjalo en true.
    // Si vas con Bearer tokens, puede ser false.
    'supports_credentials' => false,
];
