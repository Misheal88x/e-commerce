<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface ReviewRepositoryInterface
{
    public function findByProduct(int $productId, int $page, int $perPage): array;
    public function countByProduct(int $productId): int;
    public function findByUserAndProduct(int $userId, int $productId): ?array;
    public function create(array $data): int;
    public function getRatingStats(int $productId): array;
    public function findByIdAndUser(int $reviewId, int $userId): ?array;
    public function delete(int $reviewId): void;
}
