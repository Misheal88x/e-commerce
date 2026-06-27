<?php

declare(strict_types=1);

use App\Controllers\V1\Brand\BrandController;

$router->get('/api/v1/brands',               [BrandController::class, 'index']);
$router->get('/api/v1/brands/{id}',          [BrandController::class, 'show']);
$router->get('/api/v1/brands/{id}/products', [BrandController::class, 'products']);
$router->post('/api/v1/brands',              [BrandController::class, 'store']);
$router->put('/api/v1/brands/{id}',          [BrandController::class, 'update']);
$router->delete('/api/v1/brands/{id}',       [BrandController::class, 'destroy']);
