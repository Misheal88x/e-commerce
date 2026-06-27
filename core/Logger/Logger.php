<?php

declare(strict_types=1);

namespace Core\Logger;

class Logger
{
    private static array $channels = [
        'app'   => 'app.log',
        'auth'  => 'auth.log',
        'error' => 'error.log',
    ];

    public static function info(string $message, array $context = [], string $channel = 'app'): void
    {
        self::write('INFO', $message, $context, $channel);
    }

    public static function warning(string $message, array $context = [], string $channel = 'app'): void
    {
        self::write('WARNING', $message, $context, $channel);
    }

    public static function error(string $message, array $context = [], string $channel = 'error'): void
    {
        self::write('ERROR', $message, $context, $channel);
    }

    public static function auth(string $message, array $context = []): void
    {
        self::write('AUTH', $message, $context, 'auth');
    }

    public static function debug(string $message, array $context = [], string $channel = 'app'): void
    {
        if (env('APP_DEBUG', false) !== 'true') {
            return;
        }
        self::write('DEBUG', $message, $context, $channel);
    }

    private static function write(string $level, string $message, array $context, string $channel): void
    {
        $file = storage_path('logs/' . (self::$channels[$channel] ?? 'app.log'));

        $line = sprintf(
            "[%s] [%s] %s %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : ''
        );

        file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
