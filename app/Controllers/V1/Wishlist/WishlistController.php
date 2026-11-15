<?php

declare(strict_types=1);

namespace App\Controllers\V1\Wishlist;

use App\Controllers\BaseController;
use App\Services\Wishlist\WishlistService;
use App\Validators\Wishlist\WishlistProductValidator;
use Core\Request;
use Core\Response;

class WishlistController extends BaseController
{
    private WishlistService $wishlistService;

    public function __construct(Request $request, WishlistService $wishlistService)
    {
        parent::__construct($request);
        $this->wishlistService = $wishlistService;
    }

    public function index(Request $request): Response
    {
        $result = $this->wishlistService->getWishlist(
            $this->userId(),
            $this->getPage($request),
            $this->getPerPage($request)
        );

        return $this->paginated($result, 'Wishlist retrieved.');
    }

    public function check(Request $request): Response
    {
        $productId = (int) $request->param('product_id');
        $result    = $this->wishlistService->isInWishlist($this->userId(), $productId);

        return $this->success($result, 'Wishlist status retrieved.');
    }

    public function store(Request $request): Response
    {
        $data = (new WishlistProductValidator())->validate($request->body());
        $item = $this->wishlistService->add($this->userId(), (int) $data['product_id']);

        return $this->created($item, 'Product added to wishlist.');
    }

    public function toggle(Request $request): Response
    {
        $data   = (new WishlistProductValidator())->validate($request->body());
        $result = $this->wishlistService->toggle($this->userId(), (int) $data['product_id']);

        $message = $result['action'] === 'added'
            ? 'Product added to wishlist.'
            : 'Product removed from wishlist.';

        return $this->success($result, $message);
    }

    public function destroy(Request $request): Response
    {
        $productId = (int) $request->param('product_id');
        $this->wishlistService->remove($this->userId(), $productId);

        return $this->deleted('Product removed from wishlist.');
    }

    public function clear(Request $request): Response
    {
        $removed = $this->wishlistService->clear($this->userId());

        return $this->success(['removed_count' => $removed], 'Wishlist cleared.');
    }
}
