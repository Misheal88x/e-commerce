<?php

declare(strict_types=1);

namespace App\Validators\Wishlist;

use App\Validators\BaseValidator;

class WishlistProductValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'product_id' => 'required|integer|min_val:1',
        ];
    }
}
