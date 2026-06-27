<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findByEmail(string $email): ?array;
    public function existsByEmail(string $email): bool;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function softDelete(int $id): bool;
}
