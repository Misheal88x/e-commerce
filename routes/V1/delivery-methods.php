<?php

declare(strict_types=1);

use App\Controllers\V1\DeliveryMethod\DeliveryMethodController;

$router->get('/api/v1/delivery-methods',        [DeliveryMethodController::class, 'index']);
$router->get('/api/v1/delivery-methods/{id}',   [DeliveryMethodController::class, 'show']);
$router->post('/api/v1/delivery-methods',       [DeliveryMethodController::class, 'store']);
$router->put('/api/v1/delivery-methods/{id}',   [DeliveryMethodController::class, 'update']);
$router->delete('/api/v1/delivery-methods/{id}',[DeliveryMethodController::class, 'destroy']);
