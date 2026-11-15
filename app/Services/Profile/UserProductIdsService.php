<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\WishlistRepositoryInterface;

class UserProductIdsService
{
    public function __construct(
        private readonly CartRepositoryInterface     $cartRepo,
        private readonly WishlistRepositoryInterface $wishlistRepo,
    ) {}

    /** @return array{cart_product_ids: int[], wishlist_product_ids: int[]} */
    public function getIds(int $userId): array
    {
        return [
            'cart_product_ids'     => $this->cartRepo->findProductIdsByUserId($userId),
            'wishlist_product_ids' => $this->wishlistRepo->findProductIdsByUser($userId),
        ];
    }
}
