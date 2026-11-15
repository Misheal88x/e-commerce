<?php

declare(strict_types=1);

namespace App\Services\Wishlist;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\WishlistRepositoryInterface;
use App\Helpers\PaginationHelper;
use App\Exceptions\HttpException;
use App\Exceptions\NotFoundException;

class WishlistService
{
    public function __construct(
        private readonly WishlistRepositoryInterface $wishlistRepo,
        private readonly ProductRepositoryInterface  $productRepo,
    ) {}

    public function getWishlist(int $userId, int $page, int $perPage): array
    {
        $result = $this->wishlistRepo->findByUser($userId, $page, $perPage);
        $items  = array_map([$this, 'formatItem'], $result['items']);

        return PaginationHelper::wrap($items, $result['total'], $page, $perPage);
    }

    public function isInWishlist(int $userId, int $productId): array
    {
        return [
            'product_id'  => $productId,
            'in_wishlist' => $this->wishlistRepo->exists($userId, $productId),
        ];
    }

    public function add(int $userId, int $productId): array
    {
        $this->assertProductExists($productId);

        if (!$this->wishlistRepo->add($userId, $productId)) {
            throw new HttpException('Product is already in your wishlist.', 409);
        }

        return $this->formatItem($this->wishlistRepo->findItem($userId, $productId));
    }

    /**
     * Adds the product if absent, removes it if present.
     *
     * @return array{action: string, in_wishlist: bool, product_id: int}
     */
    public function toggle(int $userId, int $productId): array
    {
        // Removal is always allowed, even if the product was deactivated meanwhile.
        if ($this->wishlistRepo->remove($userId, $productId)) {
            return ['action' => 'removed', 'in_wishlist' => false, 'product_id' => $productId];
        }

        $this->assertProductExists($productId);
        $this->wishlistRepo->add($userId, $productId);

        return ['action' => 'added', 'in_wishlist' => true, 'product_id' => $productId];
    }

    public function remove(int $userId, int $productId): void
    {
        if (!$this->wishlistRepo->remove($userId, $productId)) {
            throw new NotFoundException('Product is not in your wishlist.');
        }
    }

    public function clear(int $userId): int
    {
        return $this->wishlistRepo->clear($userId);
    }

    private function assertProductExists(int $productId): void
    {
        if (!$this->productRepo->findById($productId)) {
            throw new NotFoundException('Product not found.');
        }
    }

    private function formatItem(array $row): array
    {
        $row['in_stock'] = (int) $row['stock_quantity'] > 0;

        return $row;
    }
}
