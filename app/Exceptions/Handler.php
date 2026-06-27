<?php

declare(strict_types=1);

namespace App\Exceptions;

use Core\Logger\Logger;
use Core\Response;
use Throwable;

class Handler
{
    public static function handle(Throwable $e): Response
    {
        return match(true) {
            $e instanceof ValidationException     => self::validationResponse($e),
            $e instanceof AuthenticationException => self::errorResponse(401, $e->getMessage()),
            $e instanceof AuthorizationException  => self::errorResponse(403, $e->getMessage()),
            $e instanceof NotFoundException       => self::errorResponse(404, $e->getMessage()),
            $e instanceof RateLimitException      => self::errorResponse(429, $e->getMessage()),
            $e instanceof HttpException           => self::errorResponse($e->getStatusCode(), $e->getMessage()),
            $e instanceof DatabaseException       => self::serverError($e),
            default                               => self::serverError($e),
        };
    }

    private static function validationResponse(ValidationException $e): Response
    {
        return Response::json([
            'success' => false,
            'message' => $e->getMessage(),
            'errors'  => $e->getErrors(),
        ], 422);
    }

    private static function errorResponse(int $status, string $message): Response
    {
        return Response::json([
            'success' => false,
            'message' => $message,
        ], $status);
    }

    private static function serverError(Throwable $e): Response
    {
        Logger::error($e->getMessage(), [
            'file'  => $e->getFile(),
            'line'  => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $message = config('app')['debug']
            ? $e->getMessage()
            : 'An unexpected error occurred.';

        return Response::json([
            'success' => false,
            'message' => $message,
        ], 500);
    }
}
