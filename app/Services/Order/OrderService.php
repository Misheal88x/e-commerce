<?php

declare(strict_types=1);

namespace App\Services\Order;

use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Helpers\PaginationHelper;
use App\Enums\OrderStatus;
use App\Exceptions\NotFoundException;
use App\Exceptions\HttpException;
use Core\Logger\Logger;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepo
    ) {}

    public function getUserOrders(int $userId, int $page, int $perPage): array
    {
        $total = $this->orderRepo->countByUserId($userId);
        $items = $this->orderRepo->findByUserId($userId, $page, $perPage);
        return PaginationHelper::wrap($items, $total, $page, $perPage);
    }

    public function getOrderDetail(int $orderId, int $userId): array
    {
        $order = $this->orderRepo->findByIdAndUserId($orderId, $userId);

        if (!$order) {
            throw new NotFoundException('Order not found.');
        }

        $order['items']          = $this->orderRepo->findItems($orderId);
        $order['status_history'] = $this->orderRepo->findStatusHistory($orderId);

        return $order;
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

        Logger::info('Order canceled', ['order_id' => $orderId, 'user_id' => $userId]);

        return $this->orderRepo->findByIdAndUserId($orderId, $userId);
    }
}
