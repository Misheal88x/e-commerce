<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;

class MaintenanceMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (config('app')['maintenance'] === true) {
            return Response::json([
                'success' => false,
                'message' => 'The service is temporarily unavailable. Please try again later.',
            ], 503);
        }

        return $next($request);
    }
}
