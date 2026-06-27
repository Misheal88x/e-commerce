<?php

declare(strict_types=1);

use App\Controllers\V1\Checkout\CheckoutController;
use App\Middleware\AuthMiddleware;

$router->post('/api/v1/checkout', [CheckoutController::class, 'placeOrder'], [AuthMiddleware::class]);
