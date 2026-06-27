<?php

declare(strict_types=1);

namespace App\Controllers\V1\Category;

use App\Controllers\BaseController;
use App\DTOs\Category\CategoryDTO;
use App\DTOs\Product\ProductFilterDTO;
use App\Services\Category\CategoryService;
use App\Services\Product\ProductService;
use App\Validators\Category\CreateCategoryValidator;
use App\Validators\Category\UpdateCategoryValidator;
use Core\Request;
use Core\Response;

class CategoryController extends BaseController
{
    public function __construct(
        Request $request,
        private readonly CategoryService $categoryService,
        private readonly ProductService  $productService,
    ) {
        parent::__construct($request);
    }

    public function index(Request $request): Response
    {
        $categories = $this->categoryService->getAllActive();
        return $this->success($categories, 'Categories retrieved.');
    }

    public function show(Request $request): Response
    {
        $id       = (int) $request->param('id');
        $category = $this->categoryService->getById($id);
        return $this->success($category, 'Category retrieved.');
    }

    public function products(Request $request): Response
    {
        $id     = (int) $request->param('id');
        $query  = array_merge($request->query(), ['category_id' => $id]);
        $filter = ProductFilterDTO::fromRequest($query);
        $result = $this->productService->getProducts($filter);
        return $this->paginated($result, 'Category products retrieved.');
    }

    public function store(Request $request): Response
    {
        $data      = (new CreateCategoryValidator())->validate($request->body());
        $iconFile  = $request->file('icon');
        $imageFile = $request->file('image');
        $dto       = CategoryDTO::fromArray($data);
        $category  = $this->categoryService->create($dto, $iconFile, $imageFile);
        return $this->created($category, 'Category created successfully.');
    }

    public function update(Request $request): Response
    {
        $id        = (int) $request->param('id');
        $data      = (new UpdateCategoryValidator())->validate($request->body());
        $iconFile  = $request->file('icon');
        $imageFile = $request->file('image');
        $category  = $this->categoryService->update($id, $data, $iconFile, $imageFile);
        return $this->updated($category, 'Category updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $this->categoryService->delete($id);
        return $this->deleted('Category deleted successfully.');
    }
}
