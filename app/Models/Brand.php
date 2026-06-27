<?php

declare(strict_types=1);

namespace App\Models;

class Brand
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $name,
        public readonly ?string $icon,
        public readonly ?string $image,
        public readonly bool    $isActive,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:       (int)  $data['id'],
            name:            $data['name'],
            icon:            $data['icon']  ?? null,
            image:           $data['image'] ?? null,
            isActive: (bool) $data['is_active'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'icon'      => $this->icon,
            'image'     => $this->image,
            'is_active' => $this->isActive,
        ];
    }
}
