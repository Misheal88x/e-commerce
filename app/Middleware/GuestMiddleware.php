<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;
use App\Services\Auth\TokenService;

class GuestMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Optionally resolve user from token if present, but don't require it
        $token = $request->bearerToken();

        if ($token) {
            $user = (new TokenService())->getUserByToken($token);
            if ($user) {
                $request->setUser($user);
            }
        }

        return $next($request);
    }
}
