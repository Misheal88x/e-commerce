<?php

declare(strict_types=1);

namespace App\Controllers\V1\Profile;

use App\Controllers\BaseController;
use App\Services\Profile\UserProductIdsService;
use Core\Request;
use Core\Response;

class UserProductIdsController extends BaseController
{
    private UserProductIdsService $productIdsService;

    public function __construct(Request $request, UserProductIdsService $productIdsService)
    {
        parent::__construct($request);
        $this->productIdsService = $productIdsService;
    }

    public function index(Request $request): Response
    {
        $ids = $this->productIdsService->getIds($this->userId());

        return $this->success($ids, 'Product IDs retrieved.');
    }
}
