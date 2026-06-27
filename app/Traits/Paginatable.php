<?php

declare(strict_types=1);

namespace App\Traits;

use App\Constants\Pagination;
use Core\Request;

trait Paginatable
{
    protected function getPage(Request $request): int
    {
        return max(1, (int) $request->query('page', 1));
    }

    protected function getPerPage(Request $request, int $default = Pagination::DEFAULT_PER_PAGE): int
    {
        return min(Pagination::MAX_PER_PAGE, max(1, (int) $request->query('per_page', $default)));
    }
}
