<?php

declare(strict_types=1);

namespace App\Validators\Product;

use App\Validators\BaseValidator;

class UpdateProductValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'name'           => 'nullable|max:255',
            'brand_id'       => 'nullable|integer',
            'category_id'    => 'nullable|integer',
            'description'    => 'nullable|max:5000',
            'sku'            => 'nullable|max:100',
            'price'          => 'nullable|numeric|min_val:0',
            'has_offer'      => 'nullable|boolean',
            'offer_price'    => 'nullable|numeric|min_val:0',
            'stock_quantity' => 'nullable|integer',
            'is_active'      => 'nullable|boolean',
        ];
    }
}
