<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\ReviewRepositoryInterface;
use Core\Database\BaseRepository;

class ReviewRepository extends BaseRepository implements ReviewRepositoryInterface
{
    public function findByProduct(int $productId, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        return $this->fetchAll(
            'SELECT pr.id, pr.rating, pr.comment, pr.created_at,
                    u.id AS user_id, u.fullname AS user_name, u.profile_image
             FROM product_reviews pr
             JOIN users u ON u.id = pr.user_id
             WHERE pr.product_id = ?
             ORDER BY pr.created_at DESC
             LIMIT ? OFFSET ?',
            [$productId, $perPage, $offset]
        );
    }

    public function countByProduct(int $productId): int
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM product_reviews WHERE product_id = ?',
            [$productId]
        );
    }

    public function findByUserAndProduct(int $userId, int $productId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM product_reviews WHERE user_id = ? AND product_id = ?',
            [$userId, $productId]
        );
    }

    public function create(array $data): int
    {
        return (int) $this->insert(
            'INSERT INTO product_reviews (user_id, product_id, rating, comment, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())',
            [$data['user_id'], $data['product_id'], $data['rating'], $data['comment'] ?? null]
        );
    }

    public function getRatingStats(int $productId): array
    {
        $row = $this->fetchOne(
            'SELECT
                COUNT(*)                    AS total_reviews,
                ROUND(AVG(rating), 2)       AS average_rating,
                SUM(rating = 5)             AS five_star,
                SUM(rating = 4)             AS four_star,
                SUM(rating = 3)             AS three_star,
                SUM(rating = 2)             AS two_star,
                SUM(rating = 1)             AS one_star
             FROM product_reviews WHERE product_id = ?',
            [$productId]
        );
        return $row ?? [];
    }

    public function hasPurchased(int $userId, int $productId): bool
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE o.user_id = ? AND oi.product_id = ? AND o.status = "finished"',
            [$userId, $productId]
        ) > 0;
    }
}
