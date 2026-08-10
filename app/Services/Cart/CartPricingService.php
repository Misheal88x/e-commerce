<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Repositories\Contracts\ProductOptionValueRepositoryInterface;

/**
 * Resolves the option-adjusted price of cart/order line items.
 *
 * Each item's `selected_options_json` stores a {option_id: option_value_id}
 * map. This service batch-resolves those value ids to their price_modifier
 * and folds them into the product's base price, so cart totals and order
 * snapshots stay consistent and avoid N+1 lookups.
 */
class CartPricingService
{
    public function __construct(
        private readonly ProductOptionValueRepositoryInterface $optionValueRepo,
    ) {}

    /**
     * @param array $items Rows containing at least: price, has_offer, offer_price,
     *                      quantity, selected_options_json.
     * @return array Same rows with `selected_options`, `options_price_modifier`,
     *               `unit_price` and `line_total` attached.
     */
    public function enrichItems(array $items): array
    {
        $valueIds = [];
        foreach ($items as $item) {
            foreach ($this->decodeOptions($item['selected_options_json'] ?? null) as $valueId) {
                $valueIds[$valueId] = true;
            }
        }

        $valuesById = [];
        if (!empty($valueIds)) {
            foreach ($this->optionValueRepo->findByIds(array_keys($valueIds)) as $value) {
                $valuesById[(int) $value['id']] = $value;
            }
        }

        return array_map(fn(array $item) => $this->enrichItem($item, $valuesById), $items);
    }

    public function calculateSubtotal(array $enrichedItems): float
    {
        return round(array_sum(array_column($enrichedItems, 'line_total')), 2);
    }

    private function enrichItem(array $item, array $valuesById): array
    {
        $basePrice = ((bool) ($item['has_offer'] ?? false)) && $item['offer_price'] !== null
            ? (float) $item['offer_price']
            : (float) $item['price'];

        $selectedOptions = [];
        $modifierTotal   = 0.0;

        foreach ($this->decodeOptions($item['selected_options_json'] ?? null) as $optionId => $valueId) {
            $value = $valuesById[$valueId] ?? null;
            if ($value === null) {
                continue;
            }

            $modifierTotal += (float) $value['price_modifier'];
            $selectedOptions[] = [
                'option_id'      => $optionId,
                'option_name'    => $value['option_name'],
                'value_id'       => (int) $value['id'],
                'value'          => $value['value'],
                'price_modifier' => (float) $value['price_modifier'],
            ];
        }

        $unitPrice = $basePrice + $modifierTotal;

        $item['selected_options']       = $selectedOptions;
        $item['options_price_modifier'] = round($modifierTotal, 2);
        $item['unit_price']             = round($unitPrice, 2);
        $item['line_total']             = round($unitPrice * (int) $item['quantity'], 2);

        return $item;
    }

    /**
     * @return array<int,int> option_id => option_value_id
     */
    private function decodeOptions(?string $json): array
    {
        if (!$json) {
            return [];
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            return [];
        }

        $result = [];
        foreach ($decoded as $optionId => $valueId) {
            $result[(int) $optionId] = (int) $valueId;
        }

        return $result;
    }
}
