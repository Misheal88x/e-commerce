<?php

declare(strict_types=1);

use App\Controllers\V1\Product\ProductOptionController;
use App\Controllers\V1\Product\ProductOptionValueController;

$router->get('/api/v1/products/{productId}/options',                         [ProductOptionController::class, 'index']);
$router->get('/api/v1/products/{productId}/options/{id}',                    [ProductOptionController::class, 'show']);
$router->post('/api/v1/products/{productId}/options',                        [ProductOptionController::class, 'store']);
$router->put('/api/v1/products/{productId}/options/{id}',                    [ProductOptionController::class, 'update']);
$router->delete('/api/v1/products/{productId}/options/{id}',                 [ProductOptionController::class, 'destroy']);

$router->get('/api/v1/options/{optionId}/values',                            [ProductOptionValueController::class, 'index']);
$router->get('/api/v1/options/{optionId}/values/{id}',                       [ProductOptionValueController::class, 'show']);
$router->post('/api/v1/options/{optionId}/values',                           [ProductOptionValueController::class, 'store']);
$router->put('/api/v1/options/{optionId}/values/{id}',                       [ProductOptionValueController::class, 'update']);
$router->delete('/api/v1/options/{optionId}/values/{id}',                    [ProductOptionValueController::class, 'destroy']);
