<?php

declare(strict_types=1);

namespace App\Controllers\V1\Order;

use App\Controllers\BaseController;
use App\Services\Order\OrderService;
use Core\Request;
use Core\Response;

class OrderController extends BaseController
{
    private OrderService $orderService;

    public function __construct(Request $request, OrderService $orderService)
    {
        parent::__construct($request);
        $this->orderService = $orderService;
    }

    public function index(Request $request): Response
    {
        $page    = $this->getPage($request);
        $perPage = $this->getPerPage($request);
        $status  = $request->query('status') !== null && trim((string) $request->query('status')) !== ''
            ? trim((string) $request->query('status'))
            : null;
        $result  = $this->orderService->getUserOrders($this->userId(), $page, $perPage, $status);
        return $this->paginated($result, 'Orders retrieved.');
    }

    public function show(Request $request): Response
    {
        $id    = (int) $request->param('id');
        $order = $this->orderService->getOrderDetail($id, $this->userId());
        return $this->success($order, 'Order retrieved.');
    }

    public function cancel(Request $request): Response
    {
        $id    = (int) $request->param('id');
        $order = $this->orderService->cancelOrder($id, $this->userId());
        return $this->success($order, 'Order canceled successfully.');
    }
}
