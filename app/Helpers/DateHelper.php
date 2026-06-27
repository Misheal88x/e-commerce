<?php

declare(strict_types=1);

namespace App\Helpers;

class DateHelper
{
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    public static function addDays(int $days, ?string $from = null): string
    {
        $base = $from ? strtotime($from) : time();
        return date('Y-m-d H:i:s', $base + ($days * 86400));
    }

    public static function isExpired(string $datetime): bool
    {
        return strtotime($datetime) < time();
    }

    public static function format(string $datetime, string $format = 'Y-m-d H:i:s'): string
    {
        return date($format, strtotime($datetime));
    }
}
