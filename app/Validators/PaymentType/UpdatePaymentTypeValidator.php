<?php

declare(strict_types=1);

namespace App\Validators\PaymentType;

use App\Validators\BaseValidator;

class UpdatePaymentTypeValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'name'      => 'nullable|max:255',
            'is_active' => 'nullable|boolean',
        ];
    }
}
