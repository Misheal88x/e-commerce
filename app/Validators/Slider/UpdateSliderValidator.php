<?php

declare(strict_types=1);

namespace App\Validators\Slider;

use App\Validators\BaseValidator;
use App\Enums\RedirectType;

class UpdateSliderValidator extends BaseValidator
{
    protected function rules(): array
    {
        $redirectTypes = implode(',', RedirectType::values());

        return [
            'title'          => 'nullable|max:500',
            'redirect_type'  => 'nullable|in:' . $redirectTypes,
            'redirect_value' => 'nullable|max:500',
            'is_active'      => 'nullable|boolean',
            'sort_order'     => 'nullable|integer',
        ];
    }
}
