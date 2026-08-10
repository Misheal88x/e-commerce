<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface ProductOptionValueRepositoryInterface
{
    public function findByOptionId(int $optionId): array;
    public function findById(int $id): ?array;
    public function findByIdAndOptionId(int $id, int $optionId): ?array;

    /**
     * Batch-fetches values by id, each row including the parent option's name
     * as `option_name`. Used to price-resolve selected cart/order options
     * without N+1 queries.
     */
    public function findByIds(array $ids): array;
    public function create(array $data): int;
    public function update(int $id, array $data): bool;
    public function delete(int $id): bool;
}
