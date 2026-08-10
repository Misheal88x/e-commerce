<?php

declare(strict_types=1);

namespace App\Controllers\V1\Cart;

use App\Controllers\BaseController;
use App\Services\Cart\CartService;
use App\Validators\Cart\AddToCartValidator;
use App\Validators\Cart\UpdateCartItemValidator;
use App\DTOs\Cart\AddToCartDTO;
use Core\Request;
use Core\Response;

class CartController extends BaseController
{
    private CartService $cartService;

    public function __construct(Request $request, CartService $cartService)
    {
        parent::__construct($request);
        $this->cartService = $cartService;
    }

    public function index(Request $request): Response
    {
        $cart = $this->cartService->getCart($this->userId());
        return $this->success($cart, 'Cart retrieved.');
    }

    public function add(Request $request): Response
    {
        $data = (new AddToCartValidator())->validate($request->body());
        $dto  = AddToCartDTO::fromArray($data, $this->userId());
        $cart = $this->cartService->addItem($dto);
        return $this->success($cart, 'Item added to cart.');
    }

    public function update(Request $request): Response
    {
        $itemId = (int) $request->param('item_id');
        $data   = (new UpdateCartItemValidator())->validate($request->body());
        $cart   = $this->cartService->updateItem($this->userId(), $itemId, (int) $data['quantity']);
        return $this->success($cart, 'Cart updated.');
    }

    public function remove(Request $request): Response
    {
        $itemId = (int) $request->param('item_id');
        $cart   = $this->cartService->removeItem($this->userId(), $itemId);
        return $this->success($cart, 'Item removed from cart.');
    }

    public function clear(Request $request): Response
    {
        $this->cartService->clearCart($this->userId());
        return $this->success(null, 'Cart cleared.');
    }
}
