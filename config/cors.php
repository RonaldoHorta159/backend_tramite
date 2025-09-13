<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Aquí puedes configurar las cabeceras para las peticiones Cross-Origin.
    | Simplemente descomenta la configuración de ejemplo de abajo.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://192.168.8.60:5173', // <-- ASEGÚRATE DE QUE ESTA LÍNEA ESTÉ PRESENTE Y SEA EXACTA
        'http://localhost:5173',    // Es bueno mantener esta también
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
