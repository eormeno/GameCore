<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_origins' => ['*'], // Ajusta esto en producción
    'allowed_methods' => ['*'],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
