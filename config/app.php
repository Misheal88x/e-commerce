<?php

return [
    'name'        => env('APP_NAME', 'Johnny Store'),
    'env'         => env('APP_ENV', 'production'),
    'debug'       => env('APP_DEBUG', 'false') === 'true',
    'url'         => env('APP_URL', 'http://localhost'),
    'version'     => env('APP_VERSION', '1.0.0'),
    'maintenance' => env('MAINTENANCE_MODE', 'false') === 'true',
];
