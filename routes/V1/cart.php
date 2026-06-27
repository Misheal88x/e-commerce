<?php

declare(strict_types=1);

use App\Controllers\V1\Cart\CartController;
use App\Middleware\AuthMiddleware;

$router->get('/api/v1/cart',                           [CartController::class, 'index'],  [AuthMiddleware::class]);
$router->post('/api/v1/cart/items',                    [CartController::class, 'add'],    [AuthMiddleware::class]);
$router->put('/api/v1/cart/items/{item_id}',           [CartController::class, 'update'], [AuthMiddleware::class]);
$router->delete('/api/v1/cart/items/{item_id}',        [CartController::class, 'remove'], [AuthMiddleware::class]);
$router->delete('/api/v1/cart',                        [CartController::class, 'clear'],  [AuthMiddleware::class]);
