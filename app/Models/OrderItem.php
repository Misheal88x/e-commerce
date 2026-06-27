<?php

declare(strict_types=1);

namespace App\Models;

class OrderItem
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $orderId,
        public readonly ?int    $productId,
        public readonly string  $productNameSnapshot,
        public readonly float   $productPriceSnapshot,
        public readonly ?float  $offerPriceSnapshot,
        public readonly int     $quantity,
        public readonly ?string $selectedOptionsJson,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:                   (int)   $data['id'],
            orderId:              (int)   $data['order_id'],
            productId:            isset($data['product_id']) ? (int) $data['product_id'] : null,
            productNameSnapshot:          $data['product_name_snapshot'],
            productPriceSnapshot: (float) $data['product_price_snapshot'],
            offerPriceSnapshot:   isset($data['offer_price_snapshot']) ? (float) $data['offer_price_snapshot'] : null,
            quantity:             (int)   $data['quantity'],
            selectedOptionsJson:          $data['selected_options_json'] ?? null,
        );
    }

    public function effectivePrice(): float
    {
        return $this->offerPriceSnapshot ?? $this->productPriceSnapshot;
    }

    public function lineTotal(): float
    {
        return $this->effectivePrice() * $this->quantity;
    }
}
