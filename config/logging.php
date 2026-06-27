<?php

return [
    'channel' => env('LOG_CHANNEL', 'file'),
    'level'   => env('LOG_LEVEL', 'error'),
    'path'    => storage_path('logs'),
];
