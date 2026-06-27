<?php

declare(strict_types=1);

use Core\Logger\Logger;

if (!function_exists('logger')) {
    function logger(string $message, array $context = [], string $level = 'info'): void
    {
        Logger::{$level}($message, $context);
    }
}

if (!function_exists('now')) {
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('abort')) {
    function abort(int $status, string $message): never
    {
        throw new \App\Exceptions\HttpException($message, $status);
    }
}
