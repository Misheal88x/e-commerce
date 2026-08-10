<?php

declare(strict_types=1);

namespace App\DTOs\Cart;

final class AddToCartDTO
{
    public function __construct(
        public readonly int   $userId,
        public readonly int   $productId,
        public readonly int   $quantity,
        /** @var array<int,int> Raw client input: option_id => option_value_id */
        public readonly array $selectedOptions = [],
    ) {}

    public static function fromArray(array $data, int $userId): static
    {
        $rawOptions = $data['selected_options'] ?? [];

        $selectedOptions = [];
        foreach ((is_array($rawOptions) ? $rawOptions : []) as $optionId => $valueId) {
            $selectedOptions[(int) $optionId] = (int) $valueId;
        }

        return new static(
            userId:          $userId,
            productId:       (int) $data['product_id'],
            quantity:        (int) $data['quantity'],
            selectedOptions: $selectedOptions,
        );
    }
}
