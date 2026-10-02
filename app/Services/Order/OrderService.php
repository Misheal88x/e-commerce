<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Helpers\PaginationHelper;
use App\Enums\OrderStatus;
use App\Exceptions\NotFoundException;
use App\Exceptions\HttpException;
use App\Exceptions\ValidationException;
use Core\Logger\Logger;

class OrderService
{
    /** Normal order progression; canceled/rejected are terminal branches handled separately. */
    private const FLOW = ['pending', 'preparing', 'delivering', 'finished'];

    public function __construct(
        private readonly OrderRepositoryInterface $orderRepo
    ) {}

    public function getUserOrders(int $userId, int $page, int $perPage, ?string $status = null): array
    {
        if ($status !== null && OrderStatus::tryFrom($status) === null) {
            throw new ValidationException([
                'status' => ["The status must be one of: " . implode(', ', array_column(OrderStatus::cases(), 'value')) . '.'],
            ]);
        }

        $total = $this->orderRepo->countByUserId($userId, $status);
        $items = $this->orderRepo->findByUserId($userId, $page, $perPage, $status);
        return PaginationHelper::wrap($items, $total, $page, $perPage);
    }

    public function getOrderDetail(int $orderId, int $userId): array
    {
        $order = $this->orderRepo->findByIdAndUserId($orderId, $userId);

        if (!$order) {
            throw new NotFoundException('Order not found.');
        }

        $items   = $this->orderRepo->findItems($orderId);
        $history = $this->orderRepo->findStatusHistory($orderId);

        return $this->formatOrderDetail($order, $items, $history);
    }

    public function cancelOrder(int $orderId, int $userId): array
    {
        $order = $this->orderRepo->findByIdAndUserId($orderId, $userId);

        if (!$order) {
            throw new NotFoundException('Order not found.');
        }

        $status = OrderStatus::from($order['status']);

        if (!$status->canCancel()) {
            throw new HttpException(
                "Cannot cancel an order with status: {$status->value}.",
                422
            );
        }

        $this->orderRepo->updateStatus($orderId, OrderStatus::Canceled->value);
        $this->orderRepo->addStatusHistory($orderId, OrderStatus::Canceled->value);

        Logger::info('Order canceled', ['order_id' => $orderId, 'user_id' => $userId]);

        return $this->getOrderDetail($orderId, $userId);
    }

    public function deleteOrder(int $orderId, int $userId): void
    {
        $order = $this->orderRepo->findByIdAndUserId($orderId, $userId);

        if (!$order) {
            throw new NotFoundException('Order not found.');
        }

        $this->orderRepo->deleteByIdAndUserId($orderId, $userId);

        Logger::info('Order deleted', ['order_id' => $orderId, 'user_id' => $userId]);
    }

    private function formatOrderDetail(array $order, array $items, array $history): array
    {
        $status = OrderStatus::from($order['status']);

        return [
            'id'              => (int) $order['id'],
            'status'          => $status->value,
            'status_label'    => $status->label(),
            'notes'           => $order['notes'],
            'created_at'      => $order['created_at'],
            'address'         => [
                'city_name'       => $order['address_city'],
                'street_name'     => $order['address_street'],
                'building_number' => $order['address_building'],
                'additional_info' => $order['address_info'],
                'latitude'        => $order['address_latitude']  !== null ? (float) $order['address_latitude']  : null,
                'longitude'       => $order['address_longitude'] !== null ? (float) $order['address_longitude'] : null,
            ],
            'payment_method'  => [
                'name' => $order['payment_name'],
            ],
            'delivery_method' => [
                'name'                    => $order['delivery_name'],
                'estimated_delivery_time' => $order['delivery_time'],
            ],
            'products'        => array_map(fn (array $item) => $this->formatOrderItem($item), $items),
            'prices'          => [
                'subtotal'        => (float) $order['subtotal'],
                'delivery_fee'    => (float) $order['delivery_fee'],
                'discount_amount' => (float) $order['discount_amount'],
                'coupon_code'     => $order['coupon_code'],
                'total_price'     => (float) $order['total_price'],
            ],
            'status_stepper'  => $this->buildStatusStepper($status, $history),
        ];
    }

    private function formatOrderItem(array $item): array
    {
        $price      = (float) $item['product_price_snapshot'];
        $offerPrice = $item['offer_price_snapshot'] !== null ? (float) $item['offer_price_snapshot'] : null;
        $quantity   = (int) $item['quantity'];
        $unitPrice  = $offerPrice ?? $price;

        return [
            'product_id'       => $item['product_id'] !== null ? (int) $item['product_id'] : null,
            'name'             => $item['product_name_snapshot'],
            'image'            => $item['product_image'] ?? null,
            'slug'             => $item['product_slug']   ?? null,
            'price'            => $price,
            'offer_price'      => $offerPrice,
            'quantity'         => $quantity,
            'selected_options' => $item['selected_options_json'] !== null
                ? json_decode((string) $item['selected_options_json'], true)
                : null,
            'line_total'       => round($unitPrice * $quantity, 2),
        ];
    }

    private function buildStatusStepper(OrderStatus $current, array $history): array
    {
        $historyMap = [];
        foreach ($history as $entry) {
            $historyMap[$entry['status']] ??= $entry['created_at'];
        }

        if (in_array($current, [OrderStatus::Canceled, OrderStatus::Rejected], true)) {
            $steps = [];
            foreach (self::FLOW as $step) {
                if (!isset($historyMap[$step])) {
                    break;
                }
                $steps[] = $this->stepperEntry($step, true, false, $historyMap[$step]);
            }
            $steps[] = $this->stepperEntry($current->value, true, true, $historyMap[$current->value] ?? null);
            return $steps;
        }

        $steps = [];
        foreach (self::FLOW as $step) {
            $isCurrent = $step === $current->value;
            $completed = isset($historyMap[$step]) && !$isCurrent;
            $steps[]   = $this->stepperEntry($step, $completed, $isCurrent, $historyMap[$step] ?? null);
        }
        return $steps;
    }

    private function stepperEntry(string $status, bool $completed, bool $current, ?string $date): array
    {
        return [
            'status'    => $status,
            'label'     => OrderStatus::from($status)->label(),
            'completed' => $completed,
            'current'   => $current,
            'date'      => $date,
        ];
    }
}
