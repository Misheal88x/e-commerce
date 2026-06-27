<?php

declare(strict_types=1);

namespace App\DTOs\Product;

final class ReviewDTO
{
    public function __construct(
        public readonly int     $userId,
        public readonly int     $productId,
        public readonly int     $rating,
        public readonly ?string $comment = null,
    ) {}

    public static function fromArray(array $data, int $userId, int $productId): static
    {
        return new static(
            userId:    $userId,
            productId: $productId,
            rating:    (int) $data['rating'],
            comment:   isset($data['comment']) && trim($data['comment']) !== ''
                       ? trim($data['comment'])
                       : null,
        );
    }
}
