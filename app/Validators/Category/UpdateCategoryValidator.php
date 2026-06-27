<?php

declare(strict_types=1);

namespace App\Validators\Category;

use App\Validators\BaseValidator;

class UpdateCategoryValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'name'      => 'nullable|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
