<?php

declare(strict_types=1);

namespace App\Validators\Auth;

use App\Validators\BaseValidator;

class LoginValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'email'    => 'required|email|max:255',
            'password' => 'required|min:1|max:128',
        ];
    }
}
