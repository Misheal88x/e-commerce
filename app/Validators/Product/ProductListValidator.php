<?php

declare(strict_types=1);

namespace App\Validators\Product;

use App\Validators\BaseValidator;

class ProductListValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'search'      => 'nullable|max:255',
            'category_id' => 'nullable|integer',
            'brand_id'    => 'nullable|integer',
            'sort'        => 'nullable|in:name_asc,name_desc,price_high,price_low,offer,latest_first',
            'has_offer'   => 'nullable|boolean',
            'page'        => 'nullable|integer',
            'per_page'    => 'nullable|integer',
        ];
    }
}
