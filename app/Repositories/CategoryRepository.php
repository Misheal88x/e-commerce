<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use Core\Database\BaseRepository;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function findAll(): array
    {
        return $this->fetchAll('SELECT * FROM categories ORDER BY name ASC');
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM categories WHERE id = ?', [$id]);
    }

    public function findActive(): array
    {
        return $this->fetchAll(
            'SELECT id, name, icon, image FROM categories WHERE is_active = 1 ORDER BY name ASC'
        );
    }

    public function create(array $data): int
    {
        return (int) $this->insert(
            'INSERT INTO categories (name, icon, image, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, NOW(), NOW())',
            [
                $data['name'],
                $data['icon']      ?? null,
                $data['image']     ?? null,
                (int) ($data['is_active'] ?? 1),
            ]
        );
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'icon', 'image', 'is_active'];
        $fields  = [];
        $params  = [];

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
            'UPDATE categories SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?',
            $params
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute(
            'DELETE FROM categories WHERE id = ?',
            [$id]
        ) > 0;
    }
}
