<?php

declare(strict_types=1);

namespace App\Validators\ProductOption;

use App\Validators\BaseValidator;

class CreateProductOptionValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'name'       => 'required|min:1|max:255',
            'sort_order' => 'nullable|integer',
        ];
    }
}
