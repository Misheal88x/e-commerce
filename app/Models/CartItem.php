<?php

declare(strict_types=1);

namespace App\Models;

class CartItem
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $cartId,
        public readonly int     $productId,
        public readonly int     $quantity,
        public readonly ?string $selectedOptionsJson,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:                  (int) $data['id'],
            cartId:              (int) $data['cart_id'],
            productId:           (int) $data['product_id'],
            quantity:            (int) $data['quantity'],
            selectedOptionsJson:       $data['selected_options_json'] ?? null,
        );
    }

    public function selectedOptions(): array
    {
        return $this->selectedOptionsJson
            ? (json_decode($this->selectedOptionsJson, true) ?? [])
            : [];
    }
}
