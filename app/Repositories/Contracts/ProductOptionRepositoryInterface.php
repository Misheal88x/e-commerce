<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface ProductOptionRepositoryInterface
{
    public function findByProductId(int $productId): array;
    public function findById(int $id): ?array;
    public function findByIdAndProductId(int $id, int $productId): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
