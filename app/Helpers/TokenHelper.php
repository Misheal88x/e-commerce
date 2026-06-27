<?php

declare(strict_types=1);

namespace App\Helpers;

class TokenHelper
{
    public static function generate(): string
    {
        $length = config('auth')['token_length'] ?? 64;
        return bin2hex(random_bytes($length));
    }

    public static function hash(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }
}
