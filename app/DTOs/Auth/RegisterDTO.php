<?php

declare(strict_types=1);

namespace App\DTOs\Auth;

final class RegisterDTO
{
    public function __construct(
        public readonly string  $fullname,
        public readonly string  $email,
        public readonly string  $password,
        public readonly string  $gender,
        public readonly ?string $birthdate = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            fullname:  $data['fullname'],
            email:     strtolower(trim($data['email'])),
            password:  $data['password'],
            gender:    $data['gender'],
            birthdate: $data['birthdate'] ?? null,
        );
    }
}
