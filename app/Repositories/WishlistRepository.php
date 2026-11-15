<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\WishlistRepositoryInterface;
use Core\Database\BaseRepository;

class WishlistRepository extends BaseRepository implements WishlistRepositoryInterface
{
    private const ITEM_COLUMNS = 'w.id AS wishlist_id, w.created_at AS wishlisted_at,
                p.id, p.name, p.slug, p.price, p.has_offer, p.offer_price,
                p.stock_quantity, p.main_image, p.average_rating,
                c.name AS category_name, b.name AS brand_name';

    private const ITEM_JOINS = 'FROM wishlists w
             JOIN products p ON p.id = w.product_id AND p.is_active = 1 AND p.deleted_at IS NULL
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id';

    public function findByUser(int $userId, int $page, int $perPage): array
    {
        return $this->paginate(
            'SELECT ' . self::ITEM_COLUMNS . ' ' . self::ITEM_JOINS . '
             WHERE w.user_id = ?
             ORDER BY w.created_at DESC, w.id DESC',
            [$userId],
            $page,
            $perPage
        );
    }

    public function findItem(int $userId, int $productId): ?array
    {
        return $this->fetchOne(
            'SELECT ' . self::ITEM_COLUMNS . ' ' . self::ITEM_JOINS . '
             WHERE w.user_id = ? AND w.product_id = ?',
            [$userId, $productId]
        );
    }

    public function exists(int $userId, int $productId): bool
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM wishlists WHERE user_id = ? AND product_id = ?',
            [$userId, $productId]
        ) > 0;
    }

    public function findProductIdsByUser(int $userId): array
    {
        $rows = $this->fetchAll(
            'SELECT w.product_id
             FROM wishlists w
             JOIN products p ON p.id = w.product_id AND p.is_active = 1 AND p.deleted_at IS NULL
             WHERE w.user_id = ?
             ORDER BY w.created_at DESC, w.id DESC',
            [$userId]
        );

        return array_map('intval', array_column($rows, 'product_id'));
    }

    public function add(int $userId, int $productId): bool
    {
        // INSERT IGNORE + unique key (user_id, product_id) makes this race-safe.
        return $this->execute(
            'INSERT IGNORE INTO wishlists (user_id, product_id) VALUES (?, ?)',
            [$userId, $productId]
        ) > 0;
    }

    public function remove(int $userId, int $productId): bool
    {
        return $this->execute(
            'DELETE FROM wishlists WHERE user_id = ? AND product_id = ?',
            [$userId, $productId]
        ) > 0;
    }

    public function clear(int $userId): int
    {
        return $this->execute(
            'DELETE FROM wishlists WHERE user_id = ?',
            [$userId]
        );
    }
}
