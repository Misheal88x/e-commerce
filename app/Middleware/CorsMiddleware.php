<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;

class CorsMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $cfg = config('cors');

        $response = $next($request);

        return $response
            ->withHeader('Access-Control-Allow-Origin',  $cfg['allowed_origins'])
            ->withHeader('Access-Control-Allow-Methods', $cfg['allowed_methods'])
            ->withHeader('Access-Control-Allow-Headers', $cfg['allowed_headers'])
            ->withHeader('Access-Control-Max-Age',       (string) $cfg['max_age']);
    }
}
