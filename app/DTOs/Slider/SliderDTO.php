<?php

declare(strict_types=1);

namespace App\DTOs\Slider;

final class SliderDTO
{
    public function __construct(
        public readonly string  $title,
        public readonly string  $redirectType,
        public readonly ?string $image         = null,
        public readonly ?string $redirectValue = null,
        public readonly bool    $isActive      = true,
        public readonly int     $sortOrder     = 0,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            title:         $data['title'],
            redirectType:  $data['redirect_type']  ?? 'none',
            image:         $data['image']           ?? null,
            redirectValue: $data['redirect_value']  ?? null,
            isActive:      (bool) ($data['is_active'] ?? true),
            sortOrder:     (int)  ($data['sort_order'] ?? 0),
        );
    }
}
