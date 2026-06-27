<?php

declare(strict_types=1);

namespace App\Controllers\V1\Product;

use App\Controllers\BaseController;
use App\DTOs\ProductOption\ProductOptionValueDTO;
use App\Services\ProductOption\ProductOptionValueService;
use App\Validators\ProductOption\CreateProductOptionValueValidator;
use App\Validators\ProductOption\UpdateProductOptionValueValidator;
use Core\Request;
use Core\Response;

class ProductOptionValueController extends BaseController
{
    private ProductOptionValueService $valueService;

    public function __construct(Request $request, ProductOptionValueService $valueService)
    {
        parent::__construct($request);
        $this->valueService = $valueService;
    }

    public function index(Request $request): Response
    {
        $optionId = (int) $request->param('optionId');
        $values   = $this->valueService->getByOption($optionId);
        return $this->success($values, 'Product option values retrieved.');
    }

    public function show(Request $request): Response
    {
        $optionId = (int) $request->param('optionId');
        $id       = (int) $request->param('id');
        $value    = $this->valueService->getOne($optionId, $id);
        return $this->success($value, 'Product option value retrieved.');
    }

    public function store(Request $request): Response
    {
        $optionId = (int) $request->param('optionId');
        $data     = (new CreateProductOptionValueValidator())->validate($request->body());
        $dto      = ProductOptionValueDTO::fromArray($data, $optionId);
        $value    = $this->valueService->create($dto);
        return $this->created($value, 'Product option value created successfully.');
    }

    public function update(Request $request): Response
    {
        $optionId = (int) $request->param('optionId');
        $id       = (int) $request->param('id');
        $data     = (new UpdateProductOptionValueValidator())->validate($request->body());
        $dto      = ProductOptionValueDTO::fromArray($data, $optionId);
        $value    = $this->valueService->update($optionId, $id, $dto);
        return $this->updated($value, 'Product option value updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $optionId = (int) $request->param('optionId');
        $id       = (int) $request->param('id');
        $this->valueService->delete($optionId, $id);
        return $this->deleted('Product option value deleted successfully.');
    }
}
