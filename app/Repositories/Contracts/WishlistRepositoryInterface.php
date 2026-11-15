<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface WishlistRepositoryInterface
{
    /** @return array{items: array, total: int} */
    public function findByUser(int $userId, int $page, int $perPage): array;
    public function findItem(int $userId, int $productId): ?array;
    public function exists(int $userId, int $productId): bool;

    /** @return int[] IDs of active products in the user's wishlist. */
    public function findProductIdsByUser(int $userId): array;

    /** Returns false when the product was already in the wishlist. */
    public function add(int $userId, int $productId): bool;

    /** Returns false when the product was not in the wishlist. */
    public function remove(int $userId, int $productId): bool;

    /** Returns the number of removed rows. */
    public function clear(int $userId): int;
}
