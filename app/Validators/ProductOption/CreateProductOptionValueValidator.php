<?php

declare(strict_types=1);

namespace App\Validators\ProductOption;

use App\Validators\BaseValidator;

class CreateProductOptionValueValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'value'          => 'required|min:1|max:255',
            'price_modifier' => 'nullable|numeric',
            'stock_modifier' => 'nullable|integer',
            'sort_order'     => 'nullable|integer',
        ];
    }
}
