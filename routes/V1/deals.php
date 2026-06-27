<?php

declare(strict_types=1);

use App\Controllers\V1\Deal\DealController;

$router->get('/api/v1/deals', [DealController::class, 'index']);
