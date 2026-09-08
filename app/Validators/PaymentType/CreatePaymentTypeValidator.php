<?php

declare(strict_types=1);

namespace App\Validators\PaymentType;

use App\Validators\BaseValidator;

class CreatePaymentTypeValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'name'      => 'required|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
