<?php

declare(strict_types=1);

namespace App\Controllers\V1\Brand;

use App\Controllers\BaseController;
use App\DTOs\Brand\BrandDTO;
use App\DTOs\Product\ProductFilterDTO;
use App\Services\Brand\BrandService;
use App\Services\Product\ProductService;
use App\Validators\Brand\CreateBrandValidator;
use App\Validators\Brand\UpdateBrandValidator;
use Core\Request;
use Core\Response;

class BrandController extends BaseController
{
    public function __construct(
        Request $request,
        private readonly BrandService   $brandService,
        private readonly ProductService $productService,
    ) {
        parent::__construct($request);
    }

    public function index(Request $request): Response
    {
        $brands = $this->brandService->getAllActive();
        return $this->success($brands, 'Brands retrieved.');
    }

    public function show(Request $request): Response
    {
        $id    = (int) $request->param('id');
        $brand = $this->brandService->getById($id);
        return $this->success($brand, 'Brand retrieved.');
    }

    public function products(Request $request): Response
    {
        $id     = (int) $request->param('id');
        $query  = array_merge($request->query(), ['brand_id' => $id]);
        $filter = ProductFilterDTO::fromRequest($query);
        $result = $this->productService->getProducts($filter);
        return $this->paginated($result, 'Brand products retrieved.');
    }

    public function store(Request $request): Response
    {
        $data      = (new CreateBrandValidator())->validate($request->body());
        $iconFile  = $request->file('icon');
        $imageFile = $request->file('image');
        $dto       = BrandDTO::fromArray($data);
        $brand     = $this->brandService->create($dto, $iconFile, $imageFile);
        return $this->created($brand, 'Brand created successfully.');
    }

    public function update(Request $request): Response
    {
        $id        = (int) $request->param('id');
        $data      = (new UpdateBrandValidator())->validate($request->body());
        $iconFile  = $request->file('icon');
        $imageFile = $request->file('image');
        $brand     = $this->brandService->update($id, $data, $iconFile, $imageFile);
        return $this->updated($brand, 'Brand updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $this->brandService->delete($id);
        return $this->deleted('Brand deleted successfully.');
    }
}
