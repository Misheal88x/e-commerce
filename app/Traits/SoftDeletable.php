<?php

declare(strict_types=1);

namespace App\Traits;

trait SoftDeletable
{
    protected function softDeleteCondition(): string
    {
        return 'deleted_at IS NULL';
    }

    protected function withTrashed(): string
    {
        return '1=1';
    }

    protected function onlyTrashed(): string
    {
        return 'deleted_at IS NOT NULL';
    }
}
