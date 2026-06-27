<?php

declare(strict_types=1);

namespace App\Services\Deal;

use App\Repositories\Contracts\ProductRepositoryInterface;
use App\DTOs\Product\ProductFilterDTO;
use App\Helpers\PaginationHelper;

class DealService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepo
    ) {}

    public function getDeals(int $page, int $perPage, string $sortBy = 'newest'): array
    {
        $filter = new ProductFilterDTO(
            hasOffer: true,
            sortBy:   $sortBy,
            page:     $page,
            perPage:  $perPage,
        );

        $total = $this->productRepo->countFiltered($filter);
        $items = $this->productRepo->findFiltered($filter);

        return PaginationHelper::wrap($items, $total, $page, $perPage);
    }
}
