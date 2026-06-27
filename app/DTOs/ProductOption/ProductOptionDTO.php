<?php

declare(strict_types=1);

namespace App\DTOs\ProductOption;

final class ProductOptionDTO
{
    public function __construct(
        public readonly int    $productId,
        public readonly string $name,
        public readonly int    $sortOrder = 0,
    ) {}

    public static function fromArray(array $data, int $productId): static
    {
        return new static(
            productId: $productId,
            name:      $data['name'],
            sortOrder: (int) ($data['sort_order'] ?? 0),
        );
    }
}
