<?php

declare(strict_types=1);

namespace App\Validators\ProductOption;

use App\Validators\BaseValidator;

class UpdateProductOptionValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'name'       => 'nullable|min:1|max:255',
            'sort_order' => 'nullable|integer',
        ];
    }
}
