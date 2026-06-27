<?php

declare(strict_types=1);

namespace App\Enums;

enum OrderStatus: string
{
    case Pending    = 'pending';
    case Preparing  = 'preparing';
    case Delivering = 'delivering';
    case Finished   = 'finished';
    case Canceled   = 'canceled';
    case Rejected   = 'rejected';

    public function isFinal(): bool
    {
        return in_array($this, [self::Finished, self::Canceled, self::Rejected], true);
    }

    public function canCancel(): bool
    {
        return $this === self::Pending;
    }

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
