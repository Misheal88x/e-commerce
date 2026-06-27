<?php

declare(strict_types=1);

namespace App\Services\ProductOption;

use App\DTOs\ProductOption\ProductOptionValueDTO;
use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\ProductOptionRepositoryInterface;
use App\Repositories\Contracts\ProductOptionValueRepositoryInterface;

class ProductOptionValueService
{
    public function __construct(
        private readonly ProductOptionValueRepositoryInterface $valueRepo,
        private readonly ProductOptionRepositoryInterface      $optionRepo,
    ) {}

    public function getByOption(int $optionId): array
    {
        $this->ensureOptionExists($optionId);

        return $this->valueRepo->findByOptionId($optionId);
    }

    public function getOne(int $optionId, int $id): array
    {
        $value = $this->valueRepo->findByIdAndOptionId($id, $optionId);
        if (!$value) {
            throw new NotFoundException('Product option value not found.');
        }

        return $value;
    }

    public function create(ProductOptionValueDTO $dto): array
    {
        $this->ensureOptionExists($dto->productOptionId);

        $id = $this->valueRepo->create([
            'product_option_id' => $dto->productOptionId,
            'value'             => $dto->value,
            'price_modifier'    => $dto->priceModifier,
            'stock_modifier'    => $dto->stockModifier,
            'sort_order'        => $dto->sortOrder,
        ]);

        return $this->valueRepo->findById($id);
    }

    public function update(int $optionId, int $id, ProductOptionValueDTO $dto): array
    {
        $this->getOne($optionId, $id);

        $this->valueRepo->update($id, [
            'value'          => $dto->value,
            'price_modifier' => $dto->priceModifier,
            'stock_modifier' => $dto->stockModifier,
            'sort_order'     => $dto->sortOrder,
        ]);

        return $this->valueRepo->findById($id);
    }

    public function delete(int $optionId, int $id): void
    {
        $this->getOne($optionId, $id);

        $this->valueRepo->delete($id);
    }

    private function ensureOptionExists(int $optionId): void
    {
        if (!$this->optionRepo->findById($optionId)) {
            throw new NotFoundException('Product option not found.');
        }
    }
}
