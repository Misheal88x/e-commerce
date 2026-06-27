<?php

declare(strict_types=1);

namespace App\Helpers;

class SlugHelper
{
    public static function make(string $text): string
    {
        $text = mb_strtolower(trim($text), 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        return trim($text, '-');
    }

    public static function unique(string $text, callable $existsCallback): string
    {
        $slug    = self::make($text);
        $original = $slug;
        $counter = 1;

        while ($existsCallback($slug)) {
            $slug = $original . '-' . $counter++;
        }

        return $slug;
    }
}
