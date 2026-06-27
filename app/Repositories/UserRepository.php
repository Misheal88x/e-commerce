<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\UserRepositoryInterface;
use Core\Database\BaseRepository;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function findById(int $id): ?array
    {
        $user = $this->fetchOne(
            'SELECT id, fullname, email, gender, birthdate, profile_image, created_at
             FROM users WHERE id = ? AND deleted_at IS NULL',
            [$id]
        );

        if ($user) {
            $user['profile_image'] = asset($user['profile_image']);
        }

        return $user;
    }

    public function findByEmail(string $email): ?array
    {
        return $this->fetchOne(
            'SELECT id, fullname, email, password, gender, birthdate, profile_image, created_at
             FROM users WHERE email = ? AND deleted_at IS NULL',
            [$email]
        );
    }

    public function existsByEmail(string $email): bool
    {
        $count = $this->fetchColumn(
            'SELECT COUNT(*) FROM users WHERE email = ? AND deleted_at IS NULL',
            [$email]
        );
        return (int) $count > 0;
    }

    public function create(array $data): int
    {
        return (int) $this->insert(
            'INSERT INTO users (fullname, email, password, gender, birthdate, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['fullname'],
                $data['email'],
                $data['password'],
                $data['gender'],
                $data['birthdate'] ?? null,
            ]
        );
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];

        foreach (['fullname', 'gender', 'birthdate', 'profile_image', 'password'] as $field) {
            if (array_key_exists($field, $data) && $data[$field] !== null) {
                $fields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        return $this->execute(
            'UPDATE users SET ' . implode(', ', $fields) . ', updated_at = NOW() WHERE id = ? AND deleted_at IS NULL',
            $params
        ) > 0;
    }

    public function softDelete(int $id): bool
    {
        return $this->execute(
            'UPDATE users SET
                deleted_at    = NOW(),
                email         = CONCAT("deleted_", id, "_", UNIX_TIMESTAMP(), "@removed.invalid"),
                fullname      = "Deleted User",
                profile_image = NULL,
                password      = "REDACTED"
             WHERE id = ? AND deleted_at IS NULL',
            [$id]
        ) > 0;
    }
}
