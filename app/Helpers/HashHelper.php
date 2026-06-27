<?php

declare(strict_types=1);

namespace App\Helpers;

class HashHelper
{
    public static function make(string $password): string
    {
        $cost = config('auth')['bcrypt_cost'] ?? 12;
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => $cost]);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function needsRehash(string $hash): bool
    {
        $cost = config('auth')['bcrypt_cost'] ?? 12;
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => $cost]);
    }
}
