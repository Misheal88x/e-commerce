<?php

declare(strict_types=1);

namespace App\Validators\Address;

use App\Validators\BaseValidator;

class UpdateAddressValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'city_name'       => 'nullable|min:2|max:255',
            'street_name'     => 'nullable|min:2|max:500',
            'building_number' => 'nullable|max:100',
            'additional_info' => 'nullable|max:500',
            'latitude'        => 'nullable|numeric',
            'longitude'       => 'nullable|numeric',
            'is_default'      => 'nullable|boolean',
        ];
    }
}
