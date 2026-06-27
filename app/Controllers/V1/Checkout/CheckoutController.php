<?php

declare(strict_types=1);

namespace App\Controllers\V1\Checkout;

use App\Controllers\BaseController;
use App\Services\Checkout\CheckoutService;
use App\Validators\Checkout\CheckoutValidator;
use App\DTOs\Checkout\CheckoutDTO;
use Core\Request;
use Core\Response;

class CheckoutController extends BaseController
{
    private CheckoutService $checkoutService;

    public function __construct(Request $request, CheckoutService $checkoutService)
    {
        parent::__construct($request);
        $this->checkoutService = $checkoutService;
    }

    public function placeOrder(Request $request): Response
    {
        $data  = (new CheckoutValidator())->validate($request->body());
        $dto   = CheckoutDTO::fromArray($data, $this->userId());
        $order = $this->checkoutService->placeOrder($dto);
        return $this->created($order, 'Order placed successfully.');
    }
}
