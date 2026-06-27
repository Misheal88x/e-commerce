<?php

declare(strict_types=1);

namespace App\Models;

class ProductOptionValue
{
    public function __construct(
        public readonly int    $id,
        public readonly int    $productOptionId,
        public readonly string $value,
        public readonly float  $priceModifier,
        public readonly int    $stockModifier,
        public readonly int    $sortOrder,
        public readonly string $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:              (int)   $data['id'],
            productOptionId: (int)   $data['product_option_id'],
            value:                   $data['value'],
            priceModifier:   (float) $data['price_modifier'],
            stockModifier:   (int)   $data['stock_modifier'],
            sortOrder:       (int)   $data['sort_order'],
            createdAt:               $data['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'product_option_id' => $this->productOptionId,
            'value'             => $this->value,
            'price_modifier'    => $this->priceModifier,
            'stock_modifier'    => $this->stockModifier,
            'sort_order'        => $this->sortOrder,
            'created_at'        => $this->createdAt,
        ];
    }
}
