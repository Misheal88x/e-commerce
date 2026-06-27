<?php

declare(strict_types=1);

use App\Controllers\V1\Auth\AuthController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RateLimitMiddleware;

$router->post('/api/v1/auth/register', [AuthController::class, 'register'], [RateLimitMiddleware::class]);
$router->post('/api/v1/auth/login',    [AuthController::class, 'login'],    [RateLimitMiddleware::class]);
$router->post('/api/v1/auth/logout',   [AuthController::class, 'logout'],   [AuthMiddleware::class]);
