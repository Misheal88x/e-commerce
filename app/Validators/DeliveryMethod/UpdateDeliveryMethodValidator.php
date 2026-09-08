<?php

declare(strict_types=1);

namespace App\Validators\DeliveryMethod;

use App\Validators\BaseValidator;

class UpdateDeliveryMethodValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'title'                   => 'nullable|max:255',
            'description'             => 'nullable|max:1000',
            'price'                   => 'nullable|numeric|min_val:0',
            'estimated_delivery_time' => 'nullable|max:255',
            'is_active'               => 'nullable|boolean',
        ];
    }
}
