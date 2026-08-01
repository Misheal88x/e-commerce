<?php

declare(strict_types=1);

namespace App\Services\Product;

use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\DTOs\Product\ReviewDTO;
use App\Helpers\PaginationHelper;
use App\Exceptions\NotFoundException;
use App\Exceptions\HttpException;

class ReviewService
{
    public function __construct(
        private readonly ReviewRepositoryInterface  $reviewRepo,
        private readonly ProductRepositoryInterface $productRepo,
    ) {}

    public function getReviews(int $productId, int $page, int $perPage): array
    {
        $total  = $this->reviewRepo->countByProduct($productId);
        $items  = $this->reviewRepo->findByProduct($productId, $page, $perPage);
        $stats  = $this->reviewRepo->getRatingStats($productId);
        $paged  = PaginationHelper::wrap($items, $total, $page, $perPage);

        return array_merge($paged, ['stats' => $stats]);
    }

    public function addReview(ReviewDTO $dto): array
    {
        // Verify product exists
        if (!$this->productRepo->findById($dto->productId)) {
            throw new NotFoundException('Product not found.');
        }

        // Prevent duplicate review
        if ($this->reviewRepo->findByUserAndProduct($dto->userId, $dto->productId)) {
            throw new HttpException('You have already reviewed this product.', 409);
        }

        $id = $this->reviewRepo->create([
            'user_id'    => $dto->userId,
            'product_id' => $dto->productId,
            'rating'     => $dto->rating,
            'comment'    => $dto->comment,
        ]);

        return ['id' => $id, 'message' => 'Review submitted successfully.'];
    }

    public function deleteReview(int $reviewId, int $productId, int $userId): void
    {
        $review = $this->reviewRepo->findByIdAndUser($reviewId, $userId);

        if (!$review) {
            throw new NotFoundException('Review not found.');
        }

        if ((int) $review['product_id'] !== $productId) {
            throw new NotFoundException('Review not found.');
        }

        $this->reviewRepo->delete($reviewId);
    }
}
