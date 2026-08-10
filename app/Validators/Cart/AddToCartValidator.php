<?php

declare(strict_types=1);

namespace App\Validators\Cart;

use App\Validators\BaseValidator;

class AddToCartValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'product_id'       => 'required|integer|min_val:1',
            'quantity'         => 'required|integer|min_val:1|max_val:100',
            'selected_options' => 'nullable|array',
        ];
    }
}
