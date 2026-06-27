<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap.php';

use Core\Application;
use App\Exceptions\Handler;

try {
    $app = Application::getInstance();
    $app->boot();

    require base_path('routes/api.php');

    $response = $app->run();
    $response->send();

} catch (\Throwable $e) {
    Handler::handle($e)->send();
}
