<?php

declare(strict_types=1);

namespace App\Validators\ProductOption;

use App\Validators\BaseValidator;

class UpdateProductOptionValueValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'value'          => 'nullable|min:1|max:255',
            'price_modifier' => 'nullable|numeric',
            'stock_modifier' => 'nullable|integer',
            'sort_order'     => 'nullable|integer',
        ];
    }
}
