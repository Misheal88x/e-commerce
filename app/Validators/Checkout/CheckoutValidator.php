<?php

declare(strict_types=1);

namespace App\Validators\Checkout;

use App\Validators\BaseValidator;

class CheckoutValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'address_id'       => 'required|integer|min_val:1',
            'delivery_type_id' => 'required|integer|min_val:1',
            'payment_type_id'  => 'required|integer|min_val:1',
            'coupon_code'      => 'nullable|max:100',
            'notes'            => 'nullable|max:500',
        ];
    }
}
