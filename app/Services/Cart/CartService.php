<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\DTOs\Cart\AddToCartDTO;
use App\Exceptions\NotFoundException;
use App\Exceptions\HttpException;

class CartService
{
    public function __construct(
        private readonly CartRepositoryInterface    $cartRepo,
        private readonly ProductRepositoryInterface $productRepo,
    ) {}

    public function getCart(int $userId): array
    {
        $cart  = $this->getOrCreateCart($userId);
        $items = $this->cartRepo->findItems($cart['id']);
        $total = $this->cartRepo->getCartTotal($cart['id']);

        return [
            'cart_id'     => $cart['id'],
            'total_items' => count($items),
            'subtotal'    => $total,
            'items'       => $items,
        ];
    }

    public function addItem(AddToCartDTO $dto): array
    {
        $product = $this->productRepo->findById($dto->productId);

        if (!$product) {
            throw new NotFoundException('Product not found.');
        }

        $cart = $this->getOrCreateCart($dto->userId);

        $existingItem = $this->cartRepo->findItem($cart['id'], $dto->productId);
        $totalQty     = $dto->quantity + ($existingItem ? (int) $existingItem['quantity'] : 0);

        $maxQty = (int) config('app')['cart_max_quantity'] ?? 10;
        if ($totalQty > (int) $product['stock_quantity']) {
            throw new HttpException('Requested quantity exceeds available stock.', 422);
        }

        if ($existingItem) {
            $this->cartRepo->updateItemQuantity((int) $existingItem['id'], $totalQty);
        } else {
            $this->cartRepo->addItem($cart['id'], $dto->productId, $dto->quantity, $dto->selectedOptionsJson());
        }

        return $this->getCart($dto->userId);
    }

    public function updateItem(int $userId, int $cartItemId, int $quantity): array
    {
        $item = $this->cartRepo->findItemById($cartItemId);
        $cart = $this->getOrCreateCart($userId);

        if (!$item || (int) $item['cart_id'] !== (int) $cart['id']) {
            throw new NotFoundException('Cart item not found.');
        }

        if ($quantity === 0) {
            $this->cartRepo->removeItem($cartItemId);
        } else {
            $product = $this->productRepo->findById((int) $item['product_id']);
            if ($quantity > (int) $product['stock_quantity']) {
                throw new HttpException('Requested quantity exceeds available stock.', 422);
            }
            $this->cartRepo->updateItemQuantity($cartItemId, $quantity);
        }

        return $this->getCart($userId);
    }

    public function removeItem(int $userId, int $cartItemId): array
    {
        $item = $this->cartRepo->findItemById($cartItemId);
        $cart = $this->getOrCreateCart($userId);

        if (!$item || (int) $item['cart_id'] !== (int) $cart['id']) {
            throw new NotFoundException('Cart item not found.');
        }

        $this->cartRepo->removeItem($cartItemId);
        return $this->getCart($userId);
    }

    public function clearCart(int $userId): void
    {
        $cart = $this->cartRepo->findByUserId($userId);
        if ($cart) {
            $this->cartRepo->clearCart((int) $cart['id']);
        }
    }

    private function getOrCreateCart(int $userId): array
    {
        $cart = $this->cartRepo->findByUserId($userId);
        if (!$cart) {
            $cartId = $this->cartRepo->createForUser($userId);
            $cart   = ['id' => $cartId, 'user_id' => $userId];
        }
        return $cart;
    }
}
