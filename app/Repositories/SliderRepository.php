<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\SliderRepositoryInterface;
use Core\Database\BaseRepository;

class SliderRepository extends BaseRepository implements SliderRepositoryInterface
{
    public function findAll(): array
    {
        return $this->fetchAll(
            'SELECT * FROM advertisements ORDER BY sort_order ASC, id ASC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM advertisements WHERE id = ?',
            [$id]
        );
    }

    public function findActive(): array
    {
        return $this->fetchAll(
            'SELECT * FROM advertisements WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        );
    }

    public function create(array $data): int
    {
        return (int) $this->insert(
            'INSERT INTO advertisements (title, image, redirect_type, redirect_value, is_active, sort_order, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['title'],
                $data['image'],
                $data['redirect_type'],
                $data['redirect_value'] ?? null,
                (int) ($data['is_active'] ?? 1),
                (int) ($data['sort_order'] ?? 0),
            ]
        );
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['title', 'image', 'redirect_type', 'redirect_value', 'is_active', 'sort_order'];
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
            'UPDATE advertisements SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?',
            $params
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute(
            'DELETE FROM advertisements WHERE id = ?',
            [$id]
        ) > 0;
    }
}
