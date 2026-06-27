<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\Helpers\TokenHelper;
use App\Helpers\DateHelper;
use App\Repositories\AuthRepository;

class TokenService
{
    private AuthRepository $authRepo;

    public function __construct()
    {
        $this->authRepo = new AuthRepository();
    }

    public function create(int $userId, string $ip, ?string $deviceName = null): string
    {
        $rawToken   = TokenHelper::generate();
        $tokenHash  = TokenHelper::hash($rawToken);
        $expiryDays = config('auth')['token_expiry_days'];
        $expiresAt  = DateHelper::addDays($expiryDays);

        $this->authRepo->createToken($userId, $tokenHash, $deviceName, $ip, $expiresAt);

        return $rawToken;
    }

    public function getUserByToken(string $rawToken): ?array
    {
        $tokenHash = TokenHelper::hash($rawToken);
        $user      = $this->authRepo->findUserByTokenHash($tokenHash);

        if ($user) {
            $this->authRepo->touchToken($tokenHash);
        }

        return $user;
    }

    public function invalidate(string $rawToken): void
    {
        $this->authRepo->deleteToken(TokenHelper::hash($rawToken));
    }

    public function invalidateAll(int $userId): void
    {
        $this->authRepo->deleteAllUserTokens($userId);
    }
}
