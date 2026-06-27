<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\DTOs\Product\ProductFilterDTO;

interface ProductRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findBySlug(string $slug): ?array;
    public function findFiltered(ProductFilterDTO $filter): array;
    public function countFiltered(ProductFilterDTO $filter): int;
    public function findImages(int $productId): array;
    public function findOptions(int $productId): array;
    public function findRelated(int $productId, int $limit): array;
    public function findAnyById(int $id): ?array;
    public function slugExists(string $slug): bool;
    public function slugExistsExcluding(string $slug, int $excludeId): bool;
    public function update(int $id, array $data): bool;
    public function decrementStock(int $productId, int $quantity): void;
    public function incrementStock(int $productId, int $quantity): void;
}
