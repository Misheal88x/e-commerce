<?php

declare(strict_types=1);

namespace App\DTOs\DeliveryMethod;

final class DeliveryMethodDTO
{
    public function __construct(
        public readonly string  $title,
        public readonly ?string $description,
        public readonly float   $price,
        public readonly ?string $estimatedDeliveryTime = null,
        public readonly bool    $isActive = true,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            title:                  $data['title'],
            description:            $data['description'] ?? null,
            price:          (float) $data['price'],
            estimatedDeliveryTime:  $data['estimated_delivery_time'] ?? null,
            isActive:       (bool) ($data['is_active'] ?? true),
        );
    }
}
