<?php

declare(strict_types=1);

namespace App\Models;

class Review
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $userId,
        public readonly int     $productId,
        public readonly int     $rating,
        public readonly ?string $comment,
        public readonly string  $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:        (int) $data['id'],
            userId:    (int) $data['user_id'],
            productId: (int) $data['product_id'],
            rating:    (int) $data['rating'],
            comment:         $data['comment']    ?? null,
            createdAt:       $data['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'user_id'    => $this->userId,
            'product_id' => $this->productId,
            'rating'     => $this->rating,
            'comment'    => $this->comment,
            'created_at' => $this->createdAt,
        ];
    }
}
