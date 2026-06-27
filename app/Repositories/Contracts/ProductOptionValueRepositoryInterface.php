<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface ProductOptionValueRepositoryInterface
{
    public function findByOptionId(int $optionId): array;
    public function findById(int $id): ?array;
    public function findByIdAndOptionId(int $id, int $optionId): ?array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
