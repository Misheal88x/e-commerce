<?php

declare(strict_types=1);

use App\Controllers\V1\PaymentType\PaymentTypeController;

$router->get('/api/v1/payment-types',        [PaymentTypeController::class, 'index']);
$router->get('/api/v1/payment-types/{id}',   [PaymentTypeController::class, 'show']);
$router->post('/api/v1/payment-types',       [PaymentTypeController::class, 'store']);
$router->put('/api/v1/payment-types/{id}',   [PaymentTypeController::class, 'update']);
$router->delete('/api/v1/payment-types/{id}',[PaymentTypeController::class, 'destroy']);
