<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Contracts\CartRepositoryInterface;
use Core\Database\BaseRepository;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function findByUserId(int $userId): ?array
    {
        return $this->fetchOne('SELECT * FROM carts WHERE user_id = ?', [$userId]);
    }

    public function createForUser(int $userId): int
    {
        return (int) $this->insert('INSERT INTO carts (user_id) VALUES (?)', [$userId]);
    }

    public function findItems(int $cartId): array
    {
        return $this->fetchAll(
            'SELECT ci.id, ci.product_id, ci.quantity, ci.selected_options_json,
                    p.name, p.price, p.has_offer, p.offer_price,
                    p.main_image, p.stock_quantity, p.slug,
                    CASE WHEN p.has_offer = 1 AND p.offer_price IS NOT NULL
                         THEN p.offer_price ELSE p.price END AS effective_price
             FROM cart_items ci
             JOIN products p ON p.id = ci.product_id
             WHERE ci.cart_id = ?',
            [$cartId]
        );
    }

    public function findItem(int $cartId, int $productId, string $optionsHash): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM cart_items WHERE cart_id = ? AND product_id = ? AND options_hash = ?',
            [$cartId, $productId, $optionsHash]
        );
    }

    public function findItemById(int $cartItemId): ?array
    {
        return $this->fetchOne('SELECT * FROM cart_items WHERE id = ?', [$cartItemId]);
    }

    public function addItem(int $cartId, int $productId, int $quantity, ?string $optionsJson, string $optionsHash): int
    {
        return (int) $this->insert(
            'INSERT INTO cart_items (cart_id, product_id, quantity, selected_options_json, options_hash)
             VALUES (?, ?, ?, ?, ?)',
            [$cartId, $productId, $quantity, $optionsJson, $optionsHash]
        );
    }

    public function updateItemQuantity(int $cartItemId, int $quantity): bool
    {
        return $this->execute(
            'UPDATE cart_items SET quantity = ? WHERE id = ?',
            [$quantity, $cartItemId]
        ) > 0;
    }

    public function removeItem(int $cartItemId): bool
    {
        return $this->execute('DELETE FROM cart_items WHERE id = ?', [$cartItemId]) > 0;
    }

    public function clearCart(int $cartId): void
    {
        $this->execute('DELETE FROM cart_items WHERE cart_id = ?', [$cartId]);
    }

    public function getItemCount(int $cartId): int
    {
        return (int) $this->fetchColumn(
            'SELECT COUNT(*) FROM cart_items WHERE cart_id = ?',
            [$cartId]
        );
    }
}
