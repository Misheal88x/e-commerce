<?php

declare(strict_types=1);

namespace App\Controllers\V1\Product;

use App\Controllers\BaseController;
use App\DTOs\ProductOption\ProductOptionDTO;
use App\Services\ProductOption\ProductOptionService;
use App\Validators\ProductOption\CreateProductOptionValidator;
use App\Validators\ProductOption\UpdateProductOptionValidator;
use Core\Request;
use Core\Response;

class ProductOptionController extends BaseController
{
    private ProductOptionService $optionService;

    public function __construct(Request $request, ProductOptionService $optionService)
    {
        parent::__construct($request);
        $this->optionService = $optionService;
    }

    public function index(Request $request): Response
    {
        $productId = (int) $request->param('productId');
        $options   = $this->optionService->getByProduct($productId);
        return $this->success($options, 'Product options retrieved.');
    }

    public function show(Request $request): Response
    {
        $productId = (int) $request->param('productId');
        $id        = (int) $request->param('id');
        $option    = $this->optionService->getOne($productId, $id);
        return $this->success($option, 'Product option retrieved.');
    }

    public function store(Request $request): Response
    {
        $productId = (int) $request->param('productId');
        $data      = (new CreateProductOptionValidator())->validate($request->body());
        $dto       = ProductOptionDTO::fromArray($data, $productId);
        $option    = $this->optionService->create($dto);
        return $this->created($option, 'Product option created successfully.');
    }

    public function update(Request $request): Response
    {
        $productId = (int) $request->param('productId');
        $id        = (int) $request->param('id');
        $data      = (new UpdateProductOptionValidator())->validate($request->body());
        $dto       = ProductOptionDTO::fromArray($data, $productId);
        $option    = $this->optionService->update($productId, $id, $dto);
        return $this->updated($option, 'Product option updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $productId = (int) $request->param('productId');
        $id        = (int) $request->param('id');
        $this->optionService->delete($productId, $id);
        return $this->deleted('Product option deleted successfully.');
    }
}
