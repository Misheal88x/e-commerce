<?php

declare(strict_types=1);

namespace App\Services\Checkout;

use App\DTOs\Checkout\CheckoutDTO;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\AddressRepositoryInterface;
use App\Exceptions\HttpException;
use App\Exceptions\NotFoundException;
use App\Enums\OrderStatus;
use Core\Database\Connection;
use Core\Logger\Logger;

class CheckoutService
{
    public function __construct(
        private readonly CartRepositoryInterface    $cartRepo,
        private readonly OrderRepositoryInterface   $orderRepo,
        private readonly ProductRepositoryInterface $productRepo,
        private readonly AddressRepositoryInterface $addressRepo,
    ) {}

    public function placeOrder(CheckoutDTO $dto): array
    {
        $cart = $this->cartRepo->findByUserId($dto->userId);

        if (!$cart) {
            throw new HttpException('Cart not found.', 404);
        }

        $items = $this->cartRepo->findItems((int) $cart['id']);

        if (empty($items)) {
            throw new HttpException('Cart is empty.', 422);
        }

        $address = $this->addressRepo->findByIdAndUserId($dto->addressId, $dto->userId);
        if (!$address) {
            throw new NotFoundException('Address not found.');
        }

        $db = Connection::getInstance();
        $db->beginTransaction();

        try {
            // Validate stock
            foreach ($items as $item) {
                $product = $this->productRepo->findById((int) $item['product_id']);
                if (!$product || (int) $item['quantity'] > (int) $product['stock_quantity']) {
                    throw new HttpException("Insufficient stock for: {$item['name']}", 422);
                }
            }

            // Calculate subtotal
            $subtotal = array_sum(array_map(
                fn($i) => (float) $i['effective_price'] * (int) $i['quantity'],
                $items
            ));

            // Fetch delivery fee
            $stmt = $db->prepare('SELECT name, fee, estimated_delivery_time FROM delivery_types WHERE id = ? AND is_active = 1');
            $stmt->execute([$dto->deliveryTypeId]);
            $delivery = $stmt->fetch();

            if (!$delivery) {
                throw new NotFoundException('Delivery type not found.');
            }

            // Fetch payment name
            $stmt = $db->prepare('SELECT name FROM payment_types WHERE id = ? AND is_active = 1');
            $stmt->execute([$dto->paymentTypeId]);
            $payment = $stmt->fetch();

            if (!$payment) {
                throw new NotFoundException('Payment type not found.');
            }

            $total = $subtotal + (float) $delivery['fee'];

            // Create order
            $orderId = $this->orderRepo->create([
                'user_id'          => $dto->userId,
                'address_id'       => $dto->addressId,
                'delivery_type_id' => $dto->deliveryTypeId,
                'payment_type_id'  => $dto->paymentTypeId,
                'coupon_id'        => null,
                'coupon_code'      => $dto->couponCode,
                'subtotal'         => $subtotal,
                'delivery_fee'     => (float) $delivery['fee'],
                'discount_amount'  => 0.00,
                'total_price'      => $total,
                'status'           => OrderStatus::Pending->value,
                'notes'            => $dto->notes,
                'address_city'     => $address['city_name'],
                'address_street'   => $address['street_name'],
                'address_building' => $address['building_number'],
                'address_info'     => $address['additional_info'],
                'address_latitude' => $address['latitude'],
                'address_longitude'=> $address['longitude'],
                'delivery_name'    => $delivery['name'],
                'delivery_time'    => $delivery['estimated_delivery_time'],
                'payment_name'     => $payment['name'],
            ]);

            // Create order items + decrement stock
            foreach ($items as $item) {
                $product = $this->productRepo->findById((int) $item['product_id']);
                $this->orderRepo->createItem([
                    'order_id'               => $orderId,
                    'product_id'             => $item['product_id'],
                    'product_name_snapshot'  => $item['name'],
                    'product_price_snapshot' => $item['price'],
                    'offer_price_snapshot'   => $item['has_offer'] ? $item['offer_price'] : null,
                    'quantity'               => $item['quantity'],
                    'selected_options_json'  => $item['selected_options_json'],
                ]);
                $this->productRepo->decrementStock((int) $item['product_id'], (int) $item['quantity']);
            }

            // Clear cart
            $this->cartRepo->clearCart((int) $cart['id']);

            $db->commit();

            Logger::info('Order placed', ['order_id' => $orderId, 'user_id' => $dto->userId]);

            return $this->orderRepo->findById($orderId);

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
