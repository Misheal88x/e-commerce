<?php

declare(strict_types=1);

namespace App\DTOs\Profile;

final class UpdateProfileDTO
{
    public function __construct(
        public readonly int     $userId,
        public readonly ?string $fullname  = null,
        public readonly ?string $gender    = null,
        public readonly ?string $birthdate = null,
        public readonly ?array  $imageFile = null,
    ) {}

    public static function fromArray(array $data, int $userId, ?array $imageFile = null): static
    {
        return new static(
            userId:    $userId,
            fullname:  $data['fullname']  ?? null,
            gender:    $data['gender']    ?? null,
            birthdate: $data['birthdate'] ?? null,
            imageFile: $imageFile,
        );
    }
}
