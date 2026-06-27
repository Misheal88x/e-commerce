<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Traits\ApiResponse;
use App\Traits\Paginatable;
use App\Traits\Filterable;
use Core\Request;

abstract class BaseController
{
    use ApiResponse;
    use Paginatable;
    use Filterable;

    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function userId(): int
    {
        return (int) $this->request->user()['id'];
    }

    protected function isAuthenticated(): bool
    {
        return $this->request->isAuthenticated();
    }
}
