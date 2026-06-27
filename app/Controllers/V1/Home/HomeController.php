<?php

declare(strict_types=1);

namespace App\Controllers\V1\Home;

use App\Controllers\BaseController;
use App\Services\Home\HomeService;
use Core\Request;
use Core\Response;

class HomeController extends BaseController
{
    private HomeService $homeService;

    public function __construct(Request $request, HomeService $homeService)
    {
        parent::__construct($request);
        $this->homeService = $homeService;
    }

    public function index(Request $request): Response
    {
        $userId = $request->isAuthenticated() ? $this->userId() : null;
        $data   = $this->homeService->getHomeData($userId);
        return $this->success($data, 'Home data retrieved.');
    }
}
