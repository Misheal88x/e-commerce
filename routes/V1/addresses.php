<?php

declare(strict_types=1);

use App\Controllers\V1\Address\AddressController;
use App\Middleware\AuthMiddleware;

$router->get('/api/v1/addresses',                      [AddressController::class, 'index'],      [AuthMiddleware::class]);
$router->get('/api/v1/addresses/{id}',                 [AddressController::class, 'show'],       [AuthMiddleware::class]);
$router->post('/api/v1/addresses',                     [AddressController::class, 'store'],      [AuthMiddleware::class]);
$router->put('/api/v1/addresses/{id}',                 [AddressController::class, 'update'],     [AuthMiddleware::class]);
$router->delete('/api/v1/addresses/{id}',              [AddressController::class, 'destroy'],    [AuthMiddleware::class]);
$router->post('/api/v1/addresses/{id}/set-default',    [AddressController::class, 'setDefault'], [AuthMiddleware::class]);
