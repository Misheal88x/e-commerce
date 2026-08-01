<?php

declare(strict_types=1);

namespace App\Controllers\V1\Product;

use App\Controllers\BaseController;
use App\Services\Product\ReviewService;
use App\Validators\Product\ReviewValidator;
use App\DTOs\Product\ReviewDTO;
use Core\Request;
use Core\Response;

class ReviewController extends BaseController
{
    private ReviewService $reviewService;

    public function __construct(Request $request, ReviewService $reviewService)
    {
        parent::__construct($request);
        $this->reviewService = $reviewService;
    }

    public function index(Request $request): Response
    {
        $productId = (int) $request->param('id');
        $page      = $this->getPage($request);
        $perPage   = $this->getPerPage($request);
        $result    = $this->reviewService->getReviews($productId, $page, $perPage);
        return $this->paginated($result, 'Reviews retrieved.');
    }

    public function store(Request $request): Response
    {
        $productId = (int) $request->param('id');
        $data      = (new ReviewValidator())->validate($request->body());
        $dto       = ReviewDTO::fromArray($data, $this->userId(), $productId);
        $result    = $this->reviewService->addReview($dto);
        return $this->created($result, 'Review submitted successfully.');
    }

    public function destroy(Request $request): Response
    {
        $productId = (int) $request->param('id');
        $reviewId  = (int) $request->param('reviewId');
        $this->reviewService->deleteReview($reviewId, $productId, $this->userId());
        return $this->deleted('Review deleted successfully.');
    }
}
