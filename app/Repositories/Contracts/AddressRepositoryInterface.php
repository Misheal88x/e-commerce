<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface AddressRepositoryInterface
{
    public function findByUserId(int $userId): array;
    public function findById(int $id): ?array;
    public function findByIdAndUserId(int $id, int $userId): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function softDelete(int $id): bool;
    public function unsetAllDefaults(int $userId): void;
    public function setDefault(int $id): bool;
    public function countByUserId(int $userId): int;
    public function findNextAfterDelete(int $userId, int $excludeId): ?array;
}
