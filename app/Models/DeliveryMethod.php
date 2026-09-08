<?php

declare(strict_types=1);

namespace App\Models;

class DeliveryMethod
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $title,
        public readonly ?string $description,
        public readonly float   $price,
        public readonly ?string $estimatedDeliveryTime,
        public readonly bool    $isActive,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:                    (int)   $data['id'],
            title:                         $data['name'],
            description:                   $data['description'] ?? null,
            price:                 (float) $data['fee'],
            estimatedDeliveryTime:         $data['estimated_delivery_time'] ?? null,
            isActive:              (bool)  $data['is_active'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'                      => $this->id,
            'title'                   => $this->title,
            'description'             => $this->description,
            'price'                   => $this->price,
            'estimated_delivery_time' => $this->estimatedDeliveryTime,
            'is_active'               => $this->isActive,
        ];
    }
}
