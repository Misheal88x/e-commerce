<?php

declare(strict_types=1);

use App\Controllers\V1\Profile\ProfileController;
use App\Controllers\V1\Profile\UserProductIdsController;
use App\Middleware\AuthMiddleware;

$router->get('/api/v1/profile',                        [ProfileController::class, 'show'],    [AuthMiddleware::class]);
$router->put('/api/v1/profile',                        [ProfileController::class, 'update'],  [AuthMiddleware::class]);
$router->delete('/api/v1/profile',                     [ProfileController::class, 'destroy'], [AuthMiddleware::class]);
$router->get('/api/v1/profile/product-ids',             [UserProductIdsController::class, 'index'], [AuthMiddleware::class]);
