<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface BrandRepositoryInterface
{
    public function findAll(): array;
    public function findById(int $id): ?array;
    public function findActive(): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
