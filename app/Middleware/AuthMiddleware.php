<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;
use App\Services\Auth\TokenService;
use App\Exceptions\AuthenticationException;

class AuthMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            throw new AuthenticationException('No authentication token provided.');
        }

        $user = (new TokenService())->getUserByToken($token);

        if (!$user) {
            throw new AuthenticationException('Invalid or expired token.');
        }

        $request->setUser($user);

        return $next($request);
    }
}
