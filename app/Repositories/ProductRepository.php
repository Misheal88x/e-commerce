<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\DTOs\Product\ProductFilterDTO;
use Core\Database\BaseRepository;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT p.*, c.name AS category_name, b.name AS brand_name
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.id = ? AND p.is_active = 1 AND p.deleted_at IS NULL',
            [$id]
        );
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne(
            'SELECT p.*, c.name AS category_name, c.icon AS category_icon,
                    b.name AS brand_name, b.icon AS brand_icon
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.slug = ? AND p.is_active = 1 AND p.deleted_at IS NULL',
            [$slug]
        );
    }

    public function findFiltered(ProductFilterDTO $filter): array
    {
        [$sql, $params] = $this->buildBaseQuery($filter);
        $sql .= $this->buildOrderBy($filter->sortBy);
        $sql .= ' LIMIT ? OFFSET ?';
        $params[] = $filter->perPage;
        $params[] = $filter->offset();
        return $this->fetchAll($sql, $params);
    }

    public function countFiltered(ProductFilterDTO $filter): int
    {
        [$sql, $params] = $this->buildBaseQuery($filter, count: true);
        return (int) ($this->fetchOne($sql, $params)['total'] ?? 0);
    }

    private function buildBaseQuery(ProductFilterDTO $filter, bool $count = false): array
    {
        $select = $count
            ? 'SELECT COUNT(*) AS total'
            : 'SELECT p.id, p.name, p.slug, p.price, p.has_offer, p.offer_price,
                      p.stock_quantity, p.main_image, p.average_rating,
                      p.total_reviews, p.total_sales,
                      c.name AS category_name, b.name AS brand_name';

        $sql    = "{$select}
                   FROM products p
                   LEFT JOIN categories c ON c.id = p.category_id
                   LEFT JOIN brands b ON b.id = p.brand_id
                   WHERE p.is_active = 1 AND p.deleted_at IS NULL";
        $params = [];

        if ($filter->categoryId !== null) {
            $sql .= ' AND p.category_id = ?';
            $params[] = $filter->categoryId;
        }
        if ($filter->brandId !== null) {
            $sql .= ' AND p.brand_id = ?';
            $params[] = $filter->brandId;
        }
        if ($filter->hasOffer !== null) {
            $sql .= ' AND p.has_offer = ?';
            $params[] = (int) $filter->hasOffer;
        }
        if ($filter->search !== null) {
            $sql .= ' AND p.name LIKE ?';
            $params[] = '%' . $filter->search . '%';
        }

        return [$sql, $params];
    }

    private function buildOrderBy(string $sortBy): string
    {
        return match($sortBy) {
            'name_asc'     => ' ORDER BY p.name ASC',
            'name_desc'    => ' ORDER BY p.name DESC',
            'price_high'   => ' ORDER BY p.price DESC',
            'price_low'    => ' ORDER BY p.price ASC',
            'offer'        => ' ORDER BY p.has_offer DESC, p.created_at DESC',
            default        => ' ORDER BY p.created_at DESC',
        };
    }

    public function findImages(int $productId): array
    {
        return $this->fetchAll(
            'SELECT image_url, sort_order FROM product_images
             WHERE product_id = ? ORDER BY sort_order ASC',
            [$productId]
        );
    }

    public function findOptions(int $productId): array
    {
        return $this->fetchAll(
            'SELECT po.id AS option_id, po.name AS option_name,
                    pov.id AS value_id, pov.value, pov.price_modifier
             FROM product_options po
             JOIN product_option_values pov ON pov.product_option_id = po.id
             WHERE po.product_id = ?
             ORDER BY po.sort_order, pov.sort_order',
            [$productId]
        );
    }

    public function findRelated(int $productId, int $limit): array
    {
        return $this->fetchAll(
            'SELECT p.id, p.name, p.slug, p.price, p.has_offer,
                    p.offer_price, p.main_image, p.average_rating,
                    (CASE WHEN p.category_id = (SELECT category_id FROM products WHERE id = ?) THEN 2 ELSE 0 END +
                     CASE WHEN p.brand_id    = (SELECT brand_id    FROM products WHERE id = ?) THEN 1 ELSE 0 END) AS relevance
             FROM products p
             WHERE p.id != ? AND p.is_active = 1 AND p.deleted_at IS NULL
               AND (p.category_id = (SELECT category_id FROM products WHERE id = ?)
                    OR p.brand_id = (SELECT brand_id FROM products WHERE id = ?))
             ORDER BY relevance DESC, p.total_sales DESC
             LIMIT ?',
            [$productId, $productId, $productId, $productId, $productId, $limit]
        );
    }

    public function findAnyById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT p.*, c.name AS category_name, c.icon AS category_icon,
                    b.name AS brand_name, b.icon AS brand_icon
             FROM products p
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN brands b ON b.id = p.brand_id
             WHERE p.id = ? AND p.deleted_at IS NULL',
            [$id]
        );
    }

    public function slugExists(string $slug): bool
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM products WHERE slug = ?',
            [$slug]
        ) > 0;
    }

    public function slugExistsExcluding(string $slug, int $excludeId): bool
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM products WHERE slug = ? AND id != ?',
            [$slug, $excludeId]
        ) > 0;
    }

    public function update(int $id, array $data): bool
    {
        $allowed = [
            'brand_id', 'category_id', 'name', 'slug', 'description',
            'sku', 'price', 'has_offer', 'offer_price', 'stock_quantity',
            'main_image', 'is_active',
        ];

        $fields = [];
        $params = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;

        return $this->execute(
            'UPDATE products SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?',
            $params
        ) > 0;
    }

    public function decrementStock(int $productId, int $quantity): void
    {
        $this->execute(
            'UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?',
            [$quantity, $productId]
        );
    }

    public function incrementStock(int $productId, int $quantity): void
    {
        $this->execute(
            'UPDATE products SET stock_quantity = stock_quantity + ? WHERE id = ?',
            [$quantity, $productId]
        );
    }
}
