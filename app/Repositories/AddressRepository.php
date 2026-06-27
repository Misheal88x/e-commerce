<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\AddressRepositoryInterface;
use Core\Database\BaseRepository;

class AddressRepository extends BaseRepository implements AddressRepositoryInterface
{
    public function findByUserId(int $userId): array
    {
        return $this->fetchAll(
            'SELECT * FROM addresses WHERE user_id = ? AND deleted_at IS NULL
             ORDER BY is_default DESC, created_at DESC',
            [$userId]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM addresses WHERE id = ? AND deleted_at IS NULL',
            [$id]
        );
    }

    public function findByIdAndUserId(int $id, int $userId): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM addresses WHERE id = ? AND user_id = ? AND deleted_at IS NULL',
            [$id, $userId]
        );
    }

    public function create(array $data): int
    {
        return (int) $this->insert(
            'INSERT INTO addresses (user_id, latitude, longitude, city_name, street_name,
             building_number, additional_info, is_default, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['user_id'],
                $data['latitude']        ?? null,
                $data['longitude']       ?? null,
                $data['city_name'],
                $data['street_name'],
                $data['building_number'] ?? null,
                $data['additional_info'] ?? null,
                (int) ($data['is_default'] ?? 0),
            ]
        );
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach (['latitude', 'longitude', 'city_name', 'street_name', 'building_number', 'additional_info', 'is_default'] as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) return false;

        $params[] = $id;
        return $this->execute(
            'UPDATE addresses SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ?',
            $params
        ) > 0;
    }

    public function softDelete(int $id): bool
    {
        return $this->execute(
            'UPDATE addresses SET deleted_at = NOW() WHERE id = ?',
            [$id]
        ) > 0;
    }

    public function unsetAllDefaults(int $userId): void
    {
        $this->execute(
            'UPDATE addresses SET is_default = 0 WHERE user_id = ? AND deleted_at IS NULL',
            [$userId]
        );
    }

    public function setDefault(int $id): bool
    {
        return $this->execute(
            'UPDATE addresses SET is_default = 1 WHERE id = ?',
            [$id]
        ) > 0;
    }

    public function countByUserId(int $userId): int
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM addresses WHERE user_id = ? AND deleted_at IS NULL',
            [$userId]
        );
    }

    public function findNextAfterDelete(int $userId, int $excludeId): ?array
    {
        return $this->fetchOne(
            'SELECT id FROM addresses WHERE user_id = ? AND id != ? AND deleted_at IS NULL
             ORDER BY created_at DESC LIMIT 1',
            [$userId, $excludeId]
        );
    }
}
