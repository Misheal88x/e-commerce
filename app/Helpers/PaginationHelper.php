<?php

declare(strict_types=1);

namespace App\Helpers;

class PaginationHelper
{
    public static function wrap(array $items, int $total, int $page, int $perPage): array
    {
        $lastPage = $perPage > 0 ? (int) ceil($total / $perPage) : 1;

        return [
            'data'       => $items,
            'pagination' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => max(1, $lastPage),
                'has_next'     => $page < $lastPage,
                'has_prev'     => $page > 1,
            ],
        ];
    }
}
