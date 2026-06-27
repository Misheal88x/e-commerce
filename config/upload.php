<?php

return [
    'max_size_bytes' => (int) env('UPLOAD_MAX_SIZE_MB', '5') * 1024 * 1024,
    'path'           => env('UPLOAD_PATH', 'storage/uploads'),
    'allowed_mimes'  => ['image/jpeg', 'image/png', 'image/webp'],
    'directories'    => [
        'products'   => 'products',
        'categories' => 'categories',
        'brands'     => 'brands',
        'profiles'   => 'profiles',
    ],
];
