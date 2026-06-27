<?php

declare(strict_types=1);

use App\Controllers\V1\Category\CategoryController;

$router->get('/api/v1/categories',               [CategoryController::class, 'index']);
$router->get('/api/v1/categories/{id}',          [CategoryController::class, 'show']);
$router->get('/api/v1/categories/{id}/products', [CategoryController::class, 'products']);
$router->post('/api/v1/categories',              [CategoryController::class, 'store']);
$router->put('/api/v1/categories/{id}',          [CategoryController::class, 'update']);
$router->delete('/api/v1/categories/{id}',       [CategoryController::class, 'destroy']);
