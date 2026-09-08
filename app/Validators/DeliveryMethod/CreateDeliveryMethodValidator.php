<?php

declare(strict_types=1);

namespace App\Validators\DeliveryMethod;

use App\Validators\BaseValidator;

class CreateDeliveryMethodValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'title'                   => 'required|max:255',
            'description'             => 'nullable|max:1000',
            'price'                   => 'required|numeric|min_val:0',
            'estimated_delivery_time' => 'nullable|max:255',
            'is_active'               => 'nullable|boolean',
        ];
    }
}
