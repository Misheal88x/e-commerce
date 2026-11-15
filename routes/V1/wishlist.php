<?php

declare(strict_types=1);

use App\Controllers\V1\Wishlist\WishlistController;
use App\Middleware\AuthMiddleware;

$router->get('/api/v1/wishlist',                       [WishlistController::class, 'index'],   [AuthMiddleware::class]);
$router->post('/api/v1/wishlist',                      [WishlistController::class, 'store'],   [AuthMiddleware::class]);
$router->post('/api/v1/wishlist/toggle',               [WishlistController::class, 'toggle'],  [AuthMiddleware::class]);
$router->delete('/api/v1/wishlist',                    [WishlistController::class, 'clear'],   [AuthMiddleware::class]);
$router->get('/api/v1/wishlist/{product_id}',          [WishlistController::class, 'check'],   [AuthMiddleware::class]);
$router->delete('/api/v1/wishlist/{product_id}',       [WishlistController::class, 'destroy'], [AuthMiddleware::class]);
