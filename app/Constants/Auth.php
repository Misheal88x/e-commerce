<?php

declare(strict_types=1);

namespace App\Constants;

final class Auth
{
    const TOKEN_LENGTH_BYTES  = 64;
    const MAX_TOKENS_PER_USER = 5;
    const PASSWORD_MIN_LENGTH = 8;
    const PASSWORD_MAX_LENGTH = 128;
}
