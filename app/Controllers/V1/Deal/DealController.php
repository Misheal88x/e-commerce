<?php

declare(strict_types=1);

namespace App\Controllers\V1\Deal;

use App\Controllers\BaseController;
use App\Services\Deal\DealService;
use Core\Request;
use Core\Response;

class DealController extends BaseController
{
    private DealService $dealService;

    public function __construct(Request $request, DealService $dealService)
    {
        parent::__construct($request);
        $this->dealService = $dealService;
    }

    public function index(Request $request): Response
    {
        $page    = $this->getPage($request);
        $perPage = $this->getPerPage($request);
        $sortBy  = $this->getSort($request, ['newest', 'price_asc', 'price_desc', 'popular']);
        $result  = $this->dealService->getDeals($page, $perPage, $sortBy);
        return $this->paginated($result, 'Deals retrieved.');
    }
}
