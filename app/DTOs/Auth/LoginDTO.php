<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

final class LoginDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            email:    strtolower(trim($data['email'])),
            password: $data['password'],
        );
    }
}
