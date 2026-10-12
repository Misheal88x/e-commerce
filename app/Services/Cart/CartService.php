<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProductOptionRepositoryInterface;
use App\Repositories\Contracts\ProductOptionValueRepositoryInterface;
use App\DTOs\Cart\AddToCartDTO;
use App\Exceptions\NotFoundException;
use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;

class CartService
{
    public function __construct(
        private readonly CartRepositoryInterface             $cartRepo,
        private readonly ProductRepositoryInterface           $productRepo,
        private readonly ProductOptionRepositoryInterface      $productOptionRepo,
        private readonly ProductOptionValueRepositoryInterface $productOptionValueRepo,
        private readonly CartPricingService                   $pricing,
    ) {}

    public function getCart(int $userId): array
    {
        $cart     = $this->getOrCreateCart($userId);
        $rawItems = $this->cartRepo->findItems($cart['id']);
        $items    = $this->pricing->enrichItems($rawItems);

        return [
            'cart_id'     => $cart['id'],
            'total_items' => count($items),
            'subtotal'    => $this->pricing->calculateSubtotal($items),
            'items'       => $items,
        ];
    }

    public function addItem(AddToCartDTO $dto): array
    {
        $product = $this->productRepo->findById($dto->productId);

        if (!$product) {
            throw new NotFoundException('Product not found.');
        }

        $normalizedOptions = $this->resolveSelectedOptions($dto->productId, $dto->selectedOptions);
        $optionsJson        = empty($normalizedOptions) ? null : json_encode($normalizedOptions);
        $optionsHash         = $this->hashOptions($normalizedOptions);

        $cart = $this->getOrCreateCart($dto->userId);

        $existingItem = $this->cartRepo->findItem($cart['id'], $dto->productId, $optionsHash);
        $totalQty     = $dto->quantity + ($existingItem ? (int) $existingItem['quantity'] : 0);

        if ($totalQty > (int) $product['stock_quantity']) {
            throw new HttpException('Requested quantity exceeds available stock.', 422);
        }

        if ($existingItem) {
            $this->cartRepo->updateItemQuantity((int) $existingItem['id'], $totalQty);
        } else {
            $this->cartRepo->addItem($cart['id'], $dto->productId, $dto->quantity, $optionsJson, $optionsHash);
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

    /**
     * Validates the client-supplied option selections against the product's
     * option groups: every group must have exactly one value selected, and
     * every selected value must actually belong to its group. When no
     * selection is sent at all, the first value of each group is used.
     *
     * @param array<int,int> $selectedOptions option_id => option_value_id
     * @return array<int,int> normalized, key-sorted option_id => option_value_id
     */
    private function resolveSelectedOptions(int $productId, array $selectedOptions): array
    {
        $options = $this->productOptionRepo->findByProductId($productId);

        if (empty($options)) {
            if (!empty($selectedOptions)) {
                throw new HttpException('This product does not have selectable options.', 422);
            }
            return [];
        }

        if (empty($selectedOptions)) {
            $selectedOptions = $this->defaultSelectedOptions($options);
        }

        $errors           = [];
        $normalized       = [];
        $allowedOptionIds = array_map(fn(array $o) => (int) $o['id'], $options);

        foreach ($options as $option) {
            $optionId = (int) $option['id'];

            if (!array_key_exists($optionId, $selectedOptions)) {
                $errors["option_{$optionId}"] = "Please select a value for \"{$option['name']}\".";
                continue;
            }

            $valueId = $selectedOptions[$optionId];
            $value   = $this->productOptionValueRepo->findByIdAndOptionId($valueId, $optionId);

            if (!$value) {
                $errors["option_{$optionId}"] = "Invalid value selected for \"{$option['name']}\".";
                continue;
            }

            $normalized[$optionId] = $valueId;
        }

        foreach (array_keys($selectedOptions) as $optionId) {
            if (!in_array($optionId, $allowedOptionIds, true)) {
                $errors["option_{$optionId}"] = 'This option does not belong to the product.';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors, 'Invalid product options.');
        }

        ksort($normalized);
        return $normalized;
    }

    /**
     * Picks the first value (by sort order) of every option group.
     * Groups without any values are skipped, so they surface as a
     * validation error later.
     *
     * @return array<int,int> option_id => option_value_id
     */
    private function defaultSelectedOptions(array $options): array
    {
        $defaults = [];
        foreach ($options as $option) {
            $values = $this->productOptionValueRepo->findByOptionId((int) $option['id']);
            if (!empty($values)) {
                $defaults[(int) $option['id']] = (int) $values[0]['id'];
            }
        }
        return $defaults;
    }

    private function hashOptions(array $normalizedOptions): string
    {
        return empty($normalizedOptions) ? '' : md5(json_encode($normalizedOptions));
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
