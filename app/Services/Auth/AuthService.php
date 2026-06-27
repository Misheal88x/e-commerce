<?php

declare(strict_types=1);

namespace App\Services\Auth;

use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\LoginDTO;
use App\Helpers\HashHelper;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\AuthRepository;
use App\Exceptions\AuthenticationException;
use App\Exceptions\HttpException;
use Core\Logger\Logger;

class AuthService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepo,
        private readonly TokenService            $tokenService,
        private readonly AuthRepository          $authRepo,
    ) {}

    public function register(RegisterDTO $dto, string $ip, ?string $device = null): array
    {
        if ($this->userRepo->existsByEmail($dto->email)) {
            throw new HttpException('Email address is already registered.', 409);
        }

        $userId = $this->userRepo->create([
            'fullname'  => $dto->fullname,
            'email'     => $dto->email,
            'password'  => HashHelper::make($dto->password),
            'gender'    => $dto->gender,
            'birthdate' => $dto->birthdate,
        ]);

        $user  = $this->userRepo->findById($userId);
        $token = $this->tokenService->create($userId, $ip, $device);

        Logger::auth('User registered', ['user_id' => $userId, 'email' => $dto->email]);

        return ['user' => $user, 'token' => $token];
    }

    public function login(LoginDTO $dto, string $ip, ?string $device = null): array
    {
        $user = $this->userRepo->findByEmail($dto->email);

        if (!$user || !HashHelper::verify($dto->password, $user['password'])) {
            $this->authRepo->recordFailedLogin($dto->email, $ip);
            Logger::auth('Failed login attempt', ['email' => $dto->email, 'ip' => $ip]);
            throw new AuthenticationException('Invalid email or password.');
        }

        $token = $this->tokenService->create((int) $user['id'], $ip, $device);

        // Remove password from returned user
        unset($user['password']);

        Logger::auth('User logged in', ['user_id' => $user['id']]);

        return ['user' => $user, 'token' => $token];
    }

    public function logout(string $rawToken): void
    {
        $this->tokenService->invalidate($rawToken);
        Logger::auth('User logged out');
    }
}
