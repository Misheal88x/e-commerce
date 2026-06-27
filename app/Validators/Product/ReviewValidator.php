<?php

declare(strict_types=1);

namespace App\Validators\Product;

use App\Validators\BaseValidator;

class ReviewValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'rating'  => 'required|integer|min_val:1|max_val:5',
            'comment' => 'nullable|max:1000',
        ];
    }
}
