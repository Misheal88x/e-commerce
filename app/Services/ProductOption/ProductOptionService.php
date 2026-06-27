<?php

declare(strict_types=1);

namespace App\Services\ProductOption;

use App\DTOs\ProductOption\ProductOptionDTO;
use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\ProductOptionRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;

class ProductOptionService
{
    public function __construct(
        private readonly ProductOptionRepositoryInterface $optionRepo,
        private readonly ProductRepositoryInterface       $productRepo,
    ) {}

    public function getByProduct(int $productId): array
    {
        $this->ensureProductExists($productId);

        return $this->optionRepo->findByProductId($productId);
    }

    public function getOne(int $productId, int $id): array
    {
        $option = $this->optionRepo->findByIdAndProductId($id, $productId);
        if (!$option) {
            throw new NotFoundException('Product option not found.');
        }

        return $option;
    }

    public function create(ProductOptionDTO $dto): array
    {
        $this->ensureProductExists($dto->productId);

        $id = $this->optionRepo->create([
            'product_id' => $dto->productId,
            'name'       => $dto->name,
            'sort_order' => $dto->sortOrder,
        ]);

        return $this->optionRepo->findById($id);
    }

    public function update(int $productId, int $id, ProductOptionDTO $dto): array
    {
        $this->getOne($productId, $id);

        $this->optionRepo->update($id, [
            'name'       => $dto->name,
            'sort_order' => $dto->sortOrder,
        ]);

        return $this->optionRepo->findById($id);
    }

    public function delete(int $productId, int $id): void
    {
        $this->getOne($productId, $id);

        $this->optionRepo->delete($id);
    }

    private function ensureProductExists(int $productId): void
    {
        if (!$this->productRepo->findById($productId)) {
            throw new NotFoundException('Product not found.');
        }
    }
}
