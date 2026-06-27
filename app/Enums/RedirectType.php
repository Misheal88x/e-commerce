<?php

declare(strict_types=1);

namespace App\Enums;

enum RedirectType: string
{
    case Product  = 'product';
    case Category = 'category';
    case Brand    = 'brand';
    case Url      = 'url';
    case None     = 'none';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
