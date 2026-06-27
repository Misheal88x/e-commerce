<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\DTOs\Product\ProductFilterDTO;
use App\Helpers\PaginationHelper;
use App\Helpers\SlugHelper;
use App\Helpers\FileUploadHelper;
use App\Constants\Upload;
use App\Exceptions\NotFoundException;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepo
    ) {}

    public function getProducts(ProductFilterDTO $filter): array
    {
        $total = $this->productRepo->countFiltered($filter);
        $items = $this->productRepo->findFiltered($filter);
        return PaginationHelper::wrap($items, $total, $filter->page, $filter->perPage);
    }

    public function getProductBySlug(string $slug): array
    {
        $product = $this->productRepo->findBySlug($slug);

        if (!$product) {
            throw new NotFoundException('Product not found.');
        }

        $product['images']  = $this->productRepo->findImages((int) $product['id']);
        $product['options'] = $this->groupOptions($this->productRepo->findOptions((int) $product['id']));

        return $product;
    }

    public function updateProduct(int $id, array $validated, ?array $mainImageFile): array
    {
        $product = $this->productRepo->findAnyById($id);
        if (!$product) {
            throw new NotFoundException('Product not found.');
        }

        $data = [];

        $scalarFields = [
            'brand_id', 'category_id', 'description', 'sku',
            'price', 'stock_quantity',
        ];

        foreach ($scalarFields as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $data[$field] = $validated[$field];
            }
        }

        foreach (['has_offer', 'is_active'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $data[$field] = (int) filter_var($validated[$field], FILTER_VALIDATE_BOOLEAN);
            }
        }

        if (array_key_exists('offer_price', $validated)) {
            $data['offer_price'] = $validated['offer_price'];
        }

        if (!empty($validated['name']) && $validated['name'] !== $product['name']) {
            $data['name'] = $validated['name'];
            $data['slug'] = SlugHelper::unique(
                $validated['name'],
                fn(string $slug) => $this->productRepo->slugExistsExcluding($slug, $id)
            );
        }

        if ($mainImageFile && $mainImageFile['error'] === UPLOAD_ERR_OK) {
            FileUploadHelper::delete($product['main_image']);
            $data['main_image'] = FileUploadHelper::upload($mainImageFile, Upload::DIR_PRODUCTS);
        }

        if (!empty($data)) {
            $this->productRepo->update($id, $data);
        }

        return $this->productRepo->findAnyById($id);
    }

    public function getRelated(int $productId, int $limit = 8): array
    {
        return $this->productRepo->findRelated($productId, $limit);
    }

    private function groupOptions(array $flat): array
    {
        $grouped = [];
        foreach ($flat as $row) {
            $optionId = $row['option_id'];
            if (!isset($grouped[$optionId])) {
                $grouped[$optionId] = [
                    'id'     => $optionId,
                    'name'   => $row['option_name'],
                    'values' => [],
                ];
            }
            $grouped[$optionId]['values'][] = [
                'id'             => $row['value_id'],
                'value'          => $row['value'],
                'price_modifier' => $row['price_modifier'],
            ];
        }
        return array_values($grouped);
    }
}
