<?php

declare(strict_types=1);

use App\Controllers\V1\Home\HomeController;
use App\Middleware\GuestMiddleware;

$router->get('/api/v1/home', [HomeController::class, 'index'], [GuestMiddleware::class]);
