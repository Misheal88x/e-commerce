<?php

declare(strict_types=1);

namespace App\Traits;

use Core\Request;

trait Filterable
{
    protected function getSort(Request $request, array $allowed, string $default = 'newest'): string
    {
        $sort = $request->query('sort_by', $default);
        return in_array($sort, $allowed, true) ? $sort : $default;
    }

    protected function getSearch(Request $request): ?string
    {
        $q = trim((string) $request->query('q', ''));
        return $q !== '' ? $q : null;
    }
}
