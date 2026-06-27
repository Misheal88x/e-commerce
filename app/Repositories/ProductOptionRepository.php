<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\ProductOptionRepositoryInterface;
use Core\Database\BaseRepository;

class ProductOptionRepository extends BaseRepository implements ProductOptionRepositoryInterface
{
    public function findByProductId(int $productId): array
    {
        return $this->fetchAll(
            'SELECT * FROM product_options WHERE product_id = ? ORDER BY sort_order ASC, id ASC',
            [$productId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM product_options WHERE id = ?', [$id]);
    }

    public function findByIdAndProductId(int $id, int $productId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM product_options WHERE id = ? AND product_id = ?',
            [$id, $productId]
        );
    }

    public function create(array $data): int
    {
        return (int) $this->insert(
            'INSERT INTO product_options (product_id, name, sort_order, created_at)
             VALUES (?, ?, ?, NOW())',
            [
                $data['product_id'],
                $data['name'],
                (int) ($data['sort_order'] ?? 0),
            ]
        );
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach (['name', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $params[] = $id;
        return $this->execute(
            'UPDATE product_options SET ' . implode(', ', $fields) . ' WHERE id = ?',
            $params
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM product_options WHERE id = ?', [$id]) > 0;
    }
}
