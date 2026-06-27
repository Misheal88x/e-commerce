<?php

return [
    'token_expiry_days' => (int) env('AUTH_TOKEN_EXPIRY_DAYS', '30'),
    'token_length'      => 64,
    'bcrypt_cost'       => 12,
    'rate_limit' => [
        'max'    => (int) env('RATE_LIMIT_AUTH_MAX', '5'),
        'window' => (int) env('RATE_LIMIT_AUTH_WINDOW', '900'),
    ],
];
