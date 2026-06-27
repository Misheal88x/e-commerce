<?php

declare(strict_types=1);

namespace App\Validators\Order;

use App\Validators\BaseValidator;

class CancelOrderValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'reason' => 'nullable|max:500',
        ];
    }
}
