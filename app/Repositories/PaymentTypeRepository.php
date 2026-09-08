<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\PaymentTypeRepositoryInterface;
use Core\Database\BaseRepository;

class PaymentTypeRepository extends BaseRepository implements PaymentTypeRepositoryInterface
{
    public function findAll(): array
    {
        return $this->fetchAll('SELECT * FROM payment_types ORDER BY name ASC');
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM payment_types WHERE id = ?', [$id]);
    }

    public function findActive(): array
    {
        return $this->fetchAll(
            'SELECT id, name FROM payment_types WHERE is_active = 1 ORDER BY name ASC'
        );
    }

    public function create(array $data): int
    {
        return (int) $this->insert(
            'INSERT INTO payment_types (name, is_active, created_at, updated_at)
             VALUES (?, ?, NOW(), NOW())',
            [
                $data['name'],
                (int) ($data['is_active'] ?? 1),
            ]
        );
    }

    public function update(int $id, array $data): bool
    {
        $allowed = ['name', 'is_active'];
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
            'UPDATE payment_types SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?',
            $params
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute(
            'DELETE FROM payment_types WHERE id = ?',
            [$id]
        ) > 0;
    }
}
