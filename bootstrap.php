<?php

declare(strict_types=1);

define('BASE_PATH', __DIR__);
define('APP_START', microtime(true));

// Load Composer autoloader
require BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

// Global helper functions
function base_path(string $path = ''): string
{
    return BASE_PATH . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : '');
}

function storage_path(string $path = ''): string
{
    return base_path('storage' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
}

function public_path(string $path = ''): string
{
    return base_path('public' . ($path ? DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR) : ''));
}

function config(string $key): mixed
{
    static $cache = [];
    if (!isset($cache[$key])) {
        $file = base_path("config/{$key}.php");
        if (!file_exists($file)) {
            throw new \RuntimeException("Config file [{$key}] not found.");
        }
        $cache[$key] = require $file;
    }
    return $cache[$key];
}

function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $default;
}

function asset(?string $path): ?string
{
    if ($path === null || $path === '') {
        return null;
    }
    $base = rtrim(config('app')['url'], '/');
    return $base . '/' . ltrim($path, '/');
}
