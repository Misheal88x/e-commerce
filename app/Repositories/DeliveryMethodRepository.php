<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\DeliveryMethodRepositoryInterface;
use Core\Database\BaseRepository;

class DeliveryMethodRepository extends BaseRepository implements DeliveryMethodRepositoryInterface
{
    private const SELECT_COLUMNS = 'id,
        name                    AS title,
        description,
        fee                     AS price,
        estimated_delivery_time,
        is_active,
        created_at,
        updated_at';

    public function findAll(): array
    {
        return $this->fetchAll(
            'SELECT ' . self::SELECT_COLUMNS . ' FROM delivery_types ORDER BY fee ASC'
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT ' . self::SELECT_COLUMNS . ' FROM delivery_types WHERE id = ?',
            [$id]
        );
    }

    public function findActive(): array
    {
        return $this->fetchAll(
            'SELECT ' . self::SELECT_COLUMNS . ' FROM delivery_types WHERE is_active = 1 ORDER BY fee ASC'
        );
    }

    public function create(array $data): int
    {
        return (int) $this->insert(
            'INSERT INTO delivery_types (name, description, fee, estimated_delivery_time, is_active, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['title'],
                $data['description'] ?? null,
                $data['price'],
                $data['estimated_delivery_time'] ?? null,
                (int) ($data['is_active'] ?? 1),
            ]
        );
    }

    public function update(int $id, array $data): bool
    {
        $columnMap = [
            'title'                   => 'name',
            'description'             => 'description',
            'price'                   => 'fee',
            'estimated_delivery_time' => 'estimated_delivery_time',
            'is_active'               => 'is_active',
        ];

        $fields = [];
        $params = [];

        foreach ($columnMap as $field => $column) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$column} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;

        return $this->execute(
            'UPDATE delivery_types SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?',
            $params
        ) > 0;
    }

    public function delete(int $id): bool
    {
        return $this->execute(
            'DELETE FROM delivery_types WHERE id = ?',
            [$id]
        ) > 0;
    }
}
