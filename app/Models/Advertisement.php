<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RedirectType;

class Advertisement
{
    public function __construct(
        public readonly int          $id,
        public readonly string       $title,
        public readonly string       $image,
        public readonly RedirectType $redirectType,
        public readonly ?string      $redirectValue,
        public readonly bool         $isActive,
        public readonly int          $sortOrder,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:            (int)  $data['id'],
            title:                $data['title'],
            image:                $data['image'],
            redirectType:         RedirectType::from($data['redirect_type']),
            redirectValue:        $data['redirect_value'] ?? null,
            isActive:      (bool) $data['is_active'],
            sortOrder:     (int)  $data['sort_order'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'image'          => $this->image,
            'redirect_type'  => $this->redirectType->value,
            'redirect_value' => $this->redirectValue,
            'is_active'      => $this->isActive,
            'sort_order'     => $this->sortOrder,
        ];
    }
}
