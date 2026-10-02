<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?array;
    public function findByIdAndUserId(int $id, int $userId): ?array;
    public function findByUserId(int $userId, int $page, int $perPage, ?string $status = null): array;
    public function countByUserId(int $userId, ?string $status = null): int;
    public function create(array $data): int;
    public function createItem(array $data): void;
    public function updateStatus(int $orderId, string $status): bool;
    public function findItems(int $orderId): array;
    public function findStatusHistory(int $orderId): array;
    public function addStatusHistory(int $orderId, string $status, ?string $note = null): void;
    public function deleteByIdAndUserId(int $id, int $userId): bool;
}
