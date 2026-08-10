<?php

declare(strict_types=1);

namespace App\Validators\Cart;

use App\Validators\BaseValidator;

class UpdateCartItemValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            // 0 is allowed and removes the item, matching CartService::updateItem.
            'quantity' => 'required|integer|min_val:0|max_val:100',
        ];
    }
}
