<?php

declare(strict_types=1);

use App\Controllers\V1\Product\ProductController;
use App\Controllers\V1\Product\ReviewController;
use App\Middleware\AuthMiddleware;

$router->get('/api/v1/products',                       [ProductController::class, 'index']);
$router->get('/api/v1/products/search',                [ProductController::class, 'search']);
$router->get('/api/v1/products/{slug}',                [ProductController::class, 'show']);
$router->get('/api/v1/products/{slug}/details',        [ProductController::class, 'details']);
$router->get('/api/v1/products/{id}/related',          [ProductController::class, 'related']);
$router->put('/api/v1/products/{id}',                  [ProductController::class, 'update']);
$router->get('/api/v1/products/{id}/reviews',          [ReviewController::class,  'index']);
$router->post('/api/v1/products/{id}/reviews',         [ReviewController::class,  'store'], [AuthMiddleware::class]);
$router->delete('/api/v1/products/{id}/reviews/{reviewId}', [ReviewController::class, 'destroy'], [AuthMiddleware::class]);
