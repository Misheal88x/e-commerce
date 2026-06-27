<?php

declare(strict_types=1);

namespace App\Services\Home;

use Core\Database\Connection;

class HomeService
{
    private \PDO $db;

    public function __construct()
    {
        $this->db = Connection::getInstance();
    }

    public function getHomeData(?int $userId): array
    {
        return [
            'advertisements'    => $this->getAdvertisements(),
            'popular_products'  => $this->getPopularProducts(),
            'popular_categories'=> $this->getPopularCategories(),
            'popular_brands'    => $this->getPopularBrands(),
            'recently_viewed'   => $userId ? $this->getRecentlyOrdered($userId) : [],
        ];
    }

    private function getAdvertisements(): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, title, image, redirect_type, redirect_value
             FROM advertisements WHERE is_active = 1 ORDER BY sort_order ASC'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getPopularProducts(): array
    {
        $stmt = $this->db->prepare(
            'SELECT p.id, p.name, p.slug, p.price, p.has_offer, p.offer_price,
                    p.stock_quantity, p.main_image, p.average_rating
             FROM products p
             WHERE p.is_active = 1 AND p.deleted_at IS NULL
             ORDER BY p.total_sales DESC LIMIT 10'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getPopularCategories(): array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.name, c.icon
             FROM categories c
             WHERE c.is_active = 1
             ORDER BY (
                 SELECT COALESCE(SUM(p.total_sales), 0)
                 FROM products p WHERE p.category_id = c.id AND p.is_active = 1 AND p.deleted_at IS NULL
             ) DESC LIMIT 8'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getPopularBrands(): array
    {
        $stmt = $this->db->prepare(
            'SELECT b.id, b.name, b.icon
             FROM brands b
             WHERE b.is_active = 1
             ORDER BY (
                 SELECT COALESCE(SUM(p.total_sales), 0)
                 FROM products p WHERE p.brand_id = b.id AND p.is_active = 1 AND p.deleted_at IS NULL
             ) DESC LIMIT 8'
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    private function getRecentlyOrdered(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT p.id, p.name, p.slug, p.price, p.has_offer,
                    p.offer_price, p.main_image, p.average_rating
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id AND o.user_id = ?
             JOIN products p ON p.id = oi.product_id AND p.is_active = 1 AND p.deleted_at IS NULL
             ORDER BY o.created_at DESC LIMIT 10'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
