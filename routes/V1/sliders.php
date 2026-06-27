<?php

declare(strict_types=1);

use App\Controllers\V1\Slider\SliderController;

$router->get('/api/v1/sliders',        [SliderController::class, 'index']);
$router->get('/api/v1/sliders/{id}',   [SliderController::class, 'show']);
$router->post('/api/v1/sliders',       [SliderController::class, 'store']);
$router->put('/api/v1/sliders/{id}',   [SliderController::class, 'update']);
$router->delete('/api/v1/sliders/{id}',[SliderController::class, 'destroy']);
