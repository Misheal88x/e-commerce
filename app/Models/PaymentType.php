<?php

declare(strict_types=1);

namespace App\Models;

class PaymentType
{
    public function __construct(
        public readonly int    $id,
        public readonly string $name,
        public readonly bool   $isActive,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:       (int)  $data['id'],
            name:            $data['name'],
            isActive: (bool) $data['is_active'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'is_active' => $this->isActive,
        ];
    }
}
