<?php

declare(strict_types=1);

namespace App\Controllers\V1\Auth;

use App\Controllers\BaseController;
use App\Services\Auth\AuthService;
use App\Validators\Auth\RegisterValidator;
use App\Validators\Auth\LoginValidator;
use App\DTOs\Auth\RegisterDTO;
use App\DTOs\Auth\LoginDTO;
use Core\Request;
use Core\Response;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct(Request $request, AuthService $authService)
    {
        parent::__construct($request);
        $this->authService = $authService;
    }

    public function register(Request $request): Response
    {
        $data = (new RegisterValidator())->validate($request->body());
        $dto  = RegisterDTO::fromArray($data);

        $result = $this->authService->register($dto, $request->ip(), $request->userAgent());

        return $this->created($result, 'Registration successful.');
    }

    public function login(Request $request): Response
    {
        $data = (new LoginValidator())->validate($request->body());
        $dto  = LoginDTO::fromArray($data);

        $result = $this->authService->login($dto, $request->ip(), $request->userAgent());

        return $this->success($result, 'Login successful.');
    }

    public function logout(Request $request): Response
    {
        $this->authService->logout($request->bearerToken());
        return $this->success(null, 'Logged out successfully.');
    }
}
