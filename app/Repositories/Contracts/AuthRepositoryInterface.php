<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface AuthRepositoryInterface
{
    public function createToken(int $userId, string $tokenHash, ?string $deviceName, string $ip, string $expiresAt): void;
    public function findUserByTokenHash(string $tokenHash): ?array;
    public function deleteToken(string $tokenHash): bool;
    public function deleteAllUserTokens(int $userId): void;
    public function touchToken(string $tokenHash): void;
    public function recordFailedLogin(string $email, string $ip): void;
    public function countRecentFailedAttempts(string $email, string $ip, int $windowSeconds): int;
}
