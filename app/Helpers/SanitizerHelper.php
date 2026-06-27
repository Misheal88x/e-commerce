<?php

declare(strict_types=1);

namespace App\Helpers;

class SanitizerHelper
{
    public static function string(string $value): string
    {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    public static function int(mixed $value): int
    {
        return (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function float(mixed $value): float
    {
        return (float) filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function email(string $value): string
    {
        return strtolower(trim(filter_var($value, FILTER_SANITIZE_EMAIL)));
    }

    public static function array(array $data): array
    {
        return array_map(
            fn($v) => is_string($v) ? self::string($v) : $v,
            $data
        );
    }
}
