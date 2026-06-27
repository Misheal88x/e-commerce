<?php

declare(strict_types=1);

namespace App\Exceptions;

class RateLimitException extends HttpException
{
    public function __construct(string $message = 'Too many requests. Please try again later.')
    {
        parent::__construct($message, 429);
    }
}
