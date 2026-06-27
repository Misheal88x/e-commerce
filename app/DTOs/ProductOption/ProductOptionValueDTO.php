<?php

declare(strict_types=1);

namespace App\DTOs\ProductOption;

final class ProductOptionValueDTO
{
    public function __construct(
        public readonly int    $productOptionId,
        public readonly string $value,
        public readonly float  $priceModifier = 0.0,
        public readonly int    $stockModifier = 0,
        public readonly int    $sortOrder     = 0,
    ) {}

    public static function fromArray(array $data, int $productOptionId): static
    {
        return new static(
            productOptionId: $productOptionId,
            value:           $data['value'],
            priceModifier:   (float) ($data['price_modifier'] ?? 0.0),
            stockModifier:   (int)   ($data['stock_modifier'] ?? 0),
            sortOrder:       (int)   ($data['sort_order'] ?? 0),
        );
    }
}
