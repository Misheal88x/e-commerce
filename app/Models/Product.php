<?php

declare(strict_types=1);

namespace App\Models;

class Product
{
    public function __construct(
        public readonly int     $id,
        public readonly ?int    $brandId,
        public readonly ?int    $categoryId,
        public readonly string  $name,
        public readonly string  $slug,
        public readonly ?string $description,
        public readonly ?string $sku,
        public readonly float   $price,
        public readonly bool    $hasOffer,
        public readonly ?float  $offerPrice,
        public readonly int     $stockQuantity,
        public readonly ?string $mainImage,
        public readonly bool    $isActive,
        public readonly float   $averageRating,
        public readonly int     $totalReviews,
        public readonly int     $totalSales,
        public readonly string  $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:            (int)   $data['id'],
            brandId:       isset($data['brand_id'])    ? (int)   $data['brand_id']    : null,
            categoryId:    isset($data['category_id']) ? (int)   $data['category_id'] : null,
            name:                  $data['name'],
            slug:                  $data['slug'],
            description:           $data['description']   ?? null,
            sku:                   $data['sku']            ?? null,
            price:         (float) $data['price'],
            hasOffer:      (bool)  $data['has_offer'],
            offerPrice:    isset($data['offer_price']) ? (float) $data['offer_price'] : null,
            stockQuantity: (int)   $data['stock_quantity'],
            mainImage:             $data['main_image']     ?? null,
            isActive:      (bool)  $data['is_active'],
            averageRating: (float) ($data['average_rating'] ?? 0),
            totalReviews:  (int)   ($data['total_reviews']  ?? 0),
            totalSales:    (int)   ($data['total_sales']    ?? 0),
            createdAt:             $data['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'brand_id'       => $this->brandId,
            'category_id'    => $this->categoryId,
            'name'           => $this->name,
            'slug'           => $this->slug,
            'description'    => $this->description,
            'sku'            => $this->sku,
            'price'          => $this->price,
            'has_offer'      => $this->hasOffer,
            'offer_price'    => $this->offerPrice,
            'stock_quantity' => $this->stockQuantity,
            'main_image'     => $this->mainImage,
            'is_active'      => $this->isActive,
            'average_rating' => $this->averageRating,
            'total_reviews'  => $this->totalReviews,
            'total_sales'    => $this->totalSales,
            'created_at'     => $this->createdAt,
        ];
    }

    public function effectivePrice(): float
    {
        return ($this->hasOffer && $this->offerPrice !== null)
            ? $this->offerPrice
            : $this->price;
    }

    public function inStock(): bool
    {
        return $this->stockQuantity > 0;
    }
}
