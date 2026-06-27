<?php

declare(strict_types=1);

namespace App\Middleware;

use Core\Request;
use Core\Response;
use App\Exceptions\HttpException;

class JsonMiddleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            $contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
            $hasBody       = $contentLength > 0;

            if ($hasBody) {
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                $isJson      = str_contains($contentType, 'application/json');
                $isMultipart = str_contains($contentType, 'multipart/form-data');

                if (!$isJson && !$isMultipart) {
                    throw new HttpException('Content-Type must be application/json or multipart/form-data.', 415);
                }
            }
        }

        return $next($request);
    }
}
