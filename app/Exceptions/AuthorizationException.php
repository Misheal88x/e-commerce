<?php

declare(strict_types=1);

namespace App\Exceptions;

class AuthorizationException extends HttpException
{
    public function __construct(string $message = 'Unauthorized.')
    {
        parent::__construct($message, 403);
    }
}
