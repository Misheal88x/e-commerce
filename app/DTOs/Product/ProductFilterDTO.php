<?php

declare(strict_types=1);

namespace App\DTOs\Product;

use App\Constants\Pagination;

final class ProductFilterDTO
{
    public function __construct(
        public readonly ?int    $categoryId = null,
        public readonly ?int    $brandId    = null,
        public readonly ?bool   $hasOffer   = null,
        public readonly ?string $search     = null,
        public readonly string  $sortBy     = 'newest',
        public readonly int     $page       = 1,
        public readonly int     $perPage    = Pagination::DEFAULT_PER_PAGE,
    ) {}

    public function offset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public static function fromRequest(array $query): static
    {
        $hasOffer = null;
        if (isset($query['has_offer']) && $query['has_offer'] !== '') {
            $hasOffer = filter_var($query['has_offer'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return new static(
            categoryId: isset($query['category_id']) ? (int) $query['category_id'] : null,
            brandId:    isset($query['brand_id'])    ? (int) $query['brand_id']    : null,
            hasOffer:   $hasOffer,
            search:     isset($query['search']) && trim($query['search']) !== '' ? trim($query['search']) : null,
            sortBy:     $query['sort'] ?? 'latest_first',
            page:       max(1, (int) ($query['page']     ?? 1)),
            perPage:    min(Pagination::MAX_PER_PAGE, max(1, (int) ($query['per_page'] ?? Pagination::DEFAULT_PER_PAGE))),
        );
    }
}
