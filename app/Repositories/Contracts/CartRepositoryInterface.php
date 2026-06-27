<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

interface CartRepositoryInterface
{
    public function findByUserId(int $userId): ?array;
    public function createForUser(int $userId): int;
    public function findItems(int $cartId): array;
    public function findItem(int $cartId, int $productId): ?array;
    public function findItemById(int $cartItemId): ?array;
    public function addItem(int $cartId, int $productId, int $quantity, ?string $optionsJson): int;
    public function updateItemQuantity(int $cartItemId, int $quantity): bool;
    public function removeItem(int $cartItemId): bool;
    public function clearCart(int $cartId): void;
    public function getCartTotal(int $cartId): float;
    public function getItemCount(int $cartId): int;
}
