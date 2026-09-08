<?php

declare(strict_types=1);

namespace App\DTOs\PaymentType;

final class PaymentTypeDTO
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
