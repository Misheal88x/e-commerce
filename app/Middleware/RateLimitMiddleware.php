<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;
use Core\Database\Connection;
use App\Exceptions\RateLimitException;
use Core\Logger\Logger;

class RateLimitMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        $cfg    = config('auth')['rate_limit'];
        $max    = $cfg['max'];
        $window = $cfg['window'];
        $email  = $request->body('email', '');
        $ip     = $request->ip();

        $db  = Connection::getInstance();
        $sql = 'SELECT COUNT(*) FROM failed_login_attempts
                WHERE (email = ? OR ip_address = ?)
                  AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)';

        $stmt = $db->prepare($sql);
        $stmt->execute([$email, $ip, $window]);
        $count = (int) $stmt->fetchColumn();

        if ($count >= $max) {
            Logger::auth('Rate limit exceeded', ['email' => $email, 'ip' => $ip]);
            throw new RateLimitException(
                "Too many failed attempts. Try again in " . (int) ($window / 60) . " minutes."
            );
        }

        return $next($request);
    }
}
