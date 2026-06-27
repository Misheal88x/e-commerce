<?php

declare(strict_types=1);

namespace App\Controllers\V1\Product;

use App\Controllers\BaseController;
use App\Services\Product\ProductService;
use App\DTOs\Product\ProductFilterDTO;
use App\Validators\Product\ProductListValidator;
use App\Validators\Product\UpdateProductValidator;
use Core\Request;
use Core\Response;

class ProductController extends BaseController
{
    private ProductService $productService;

    public function __construct(Request $request, ProductService $productService)
    {
        parent::__construct($request);
        $this->productService = $productService;
    }

    public function index(Request $request): Response
    {
        (new ProductListValidator())->validate($request->query());
        $filter = ProductFilterDTO::fromRequest($request->query());
        $result = $this->productService->getProducts($filter);
        return $this->paginated($result, 'Products retrieved.');
    }

    public function show(Request $request): Response
    {
        $slug    = $request->param('slug');
        $product = $this->productService->getProductBySlug($slug);
        return $this->success($product, 'Product retrieved.');
    }

    public function related(Request $request): Response
    {
        $id      = (int) $request->param('id');
        $related = $this->productService->getRelated($id);
        return $this->success($related, 'Related products retrieved.');
    }

    public function update(Request $request): Response
    {
        $id        = (int) $request->param('id');
        $data      = (new UpdateProductValidator())->validate($request->body());
        $imageFile = $request->file('main_image');
        $product   = $this->productService->updateProduct($id, $data, $imageFile);
        return $this->updated($product, 'Product updated successfully.');
    }

    public function search(Request $request): Response
    {
        $filter = ProductFilterDTO::fromRequest($request->query());
        $result = $this->productService->getProducts($filter);
        return $this->paginated($result, 'Search results retrieved.');
    }
}
