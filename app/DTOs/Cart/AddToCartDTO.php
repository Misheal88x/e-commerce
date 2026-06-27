<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

final class AddToCartDTO
{
    public function __construct(
        public readonly int    $userId,
        public readonly int    $productId,
        public readonly int    $quantity,
        public readonly ?array $selectedOptions = null,
    ) {}

    public static function fromArray(array $data, int $userId): static
    {
        return new static(
            userId:          $userId,
            productId:       (int) $data['product_id'],
            quantity:        (int) $data['quantity'],
            selectedOptions: $data['selected_options'] ?? null,
        );
    }

    public function selectedOptionsJson(): ?string
    {
        return $this->selectedOptions !== null
            ? json_encode($this->selectedOptions)
            : null;
    }
}
