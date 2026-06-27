<?php

declare(strict_types=1);

namespace App\Models;

class Cart
{
    public function __construct(
        public readonly int    $id,
        public readonly int    $userId,
        public readonly string $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:        (int) $data['id'],
            userId:    (int) $data['user_id'],
            createdAt:       $data['created_at'],
        );
    }
}
