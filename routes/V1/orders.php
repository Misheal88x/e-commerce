<?php

declare(strict_types=1);

use App\Controllers\V1\Order\OrderController;
use App\Middleware\AuthMiddleware;

$router->get('/api/v1/orders',                         [OrderController::class, 'index'],  [AuthMiddleware::class]);
$router->get('/api/v1/orders/{id}',                    [OrderController::class, 'show'],   [AuthMiddleware::class]);
$router->post('/api/v1/orders/{id}/cancel',            [OrderController::class, 'cancel'],  [AuthMiddleware::class]);
$router->delete('/api/v1/orders/{id}',                 [OrderController::class, 'destroy'], [AuthMiddleware::class]);
