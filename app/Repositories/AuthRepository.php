<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\AuthRepositoryInterface;
use Core\Database\BaseRepository;

class AuthRepository extends BaseRepository implements AuthRepositoryInterface
{
    public function createToken(int $userId, string $tokenHash, ?string $deviceName, string $ip, string $expiresAt): void
    {
        $this->execute(
            'INSERT INTO user_tokens (user_id, token, device_name, ip_address, expires_at, last_used_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [$userId, $tokenHash, $deviceName, $ip, $expiresAt]
        );
    }

    public function findUserByTokenHash(string $tokenHash): ?array
    {
        return $this->fetchOne(
            'SELECT u.id, u.fullname, u.email, u.gender, u.birthdate, u.profile_image, u.created_at
             FROM user_tokens t
             JOIN users u ON u.id = t.user_id
             WHERE t.token = ?
               AND u.deleted_at IS NULL
               AND (t.expires_at IS NULL OR t.expires_at > NOW())',
            [$tokenHash]
        );
    }

    public function deleteToken(string $tokenHash): bool
    {
        return $this->execute(
            'DELETE FROM user_tokens WHERE token = ?',
            [$tokenHash]
        ) > 0;
    }

    public function deleteAllUserTokens(int $userId): void
    {
        $this->execute(
            'DELETE FROM user_tokens WHERE user_id = ?',
            [$userId]
        );
    }

    public function touchToken(string $tokenHash): void
    {
        $this->execute(
            'UPDATE user_tokens SET last_used_at = NOW() WHERE token = ?',
            [$tokenHash]
        );
    }

    public function recordFailedLogin(string $email, string $ip): void
    {
        $this->execute(
            'INSERT INTO failed_login_attempts (email, ip_address) VALUES (?, ?)',
            [$email, $ip]
        );
    }

    public function countRecentFailedAttempts(string $email, string $ip, int $windowSeconds): int
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM failed_login_attempts
             WHERE (email = ? OR ip_address = ?)
               AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)',
            [$email, $ip, $windowSeconds]
        );
    }
}
