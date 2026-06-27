<?php

declare(strict_types=1);

namespace App\Validators\Auth;

use App\Validators\BaseValidator;
use App\Enums\Gender;

class RegisterValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'fullname'  => 'required|min:2|max:255',
            'email'     => 'required|email|max:255',
            'password'  => 'required|min:8|max:128',
            'gender'    => 'required|in:' . implode(',', Gender::values()),
            'birthdate' => 'nullable|date',
        ];
    }
}
