<?php

declare(strict_types=1);

namespace App\Validators\Profile;

use App\Validators\BaseValidator;
use App\Enums\Gender;

class UpdateProfileValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'fullname'  => 'nullable|min:2|max:255',
            'gender'    => 'nullable|in:' . implode(',', Gender::values()),
            'birthdate' => 'nullable|date',
        ];
    }
}
