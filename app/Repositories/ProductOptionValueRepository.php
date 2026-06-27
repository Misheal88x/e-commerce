<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\ProductOptionValueRepositoryInterface;
use Core\Database\BaseRepository;

class ProductOptionValueRepository extends BaseRepository implements ProductOptionValueRepositoryInterface
{
    public function findByOptionId(int $optionId): array
    {
        return $this->fetchAll(
            'SELECT * FROM product_option_values WHERE product_option_id = ? ORDER BY sort_order ASC, id ASC',
            [$optionId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM product_option_values WHERE id = ?', [$id]);
    }

    public function findByIdAndOptionId(int $id, int $optionId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM product_option_values WHERE id = ? AND product_option_id = ?',
            [$id, $optionId]
        );
    }

    public function create(array $data): int
    {
        return (int) $this->insert(
            'INSERT INTO product_option_values
             (product_option_id, value, price_modifier, stock_modifier, sort_order, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())',
            [
                $data['product_option_id'],
                $data['value'],
                (float) ($data['price_modifier'] ?? 0.0),
                (int)   ($data['stock_modifier'] ?? 0),
                (int)   ($data['sort_order'] ?? 0),
            ]
        );
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach (['value', 'price_modifier', 'stock_modifier', 'sort_order'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $params[] = $id;
        return $this->execute(
            'UPDATE product_option_values SET ' . implode(', ', $fields) . ' WHERE id = ?',
            $params
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM product_option_values WHERE id = ?', [$id]) > 0;
    }
}
