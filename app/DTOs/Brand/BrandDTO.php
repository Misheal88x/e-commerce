<?php

declare(strict_types=1);

namespace App\DTOs\Brand;

final class BrandDTO
{
    public function __construct(
        public readonly string $name,
        public readonly bool   $isActive = true,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            name:     $data['name'],
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }
}
