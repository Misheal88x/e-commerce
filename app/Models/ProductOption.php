<?php

declare(strict_types=1);

namespace App\Models;

class ProductOption
{
    public function __construct(
        public readonly int    $id,
        public readonly int    $productId,
        public readonly string $name,
        public readonly int    $sortOrder,
        public readonly string $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:        (int) $data['id'],
            productId: (int) $data['product_id'],
            name:            $data['name'],
            sortOrder: (int) $data['sort_order'],
            createdAt:       $data['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'product_id' => $this->productId,
            'name'       => $this->name,
            'sort_order' => $this->sortOrder,
            'created_at' => $this->createdAt,
        ];
    }
}
