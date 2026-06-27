<?php

declare(strict_types=1);

namespace App\Exceptions;

class DatabaseException extends HttpException
{
    public function __construct(string $message = 'A database error occurred.')
    {
        parent::__construct($message, 500);
    }
}
