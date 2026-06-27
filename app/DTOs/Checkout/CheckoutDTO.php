<?php

declare(strict_types=1);

namespace App\DTOs\Checkout;

final class CheckoutDTO
{
    public function __construct(
        public readonly int     $userId,
        public readonly int     $addressId,
        public readonly int     $deliveryTypeId,
        public readonly int     $paymentTypeId,
        public readonly ?string $couponCode = null,
        public readonly ?string $notes      = null,
    ) {}

    public static function fromArray(array $data, int $userId): static
    {
        return new static(
            userId:         $userId,
            addressId:      (int) $data['address_id'],
            deliveryTypeId: (int) $data['delivery_type_id'],
            paymentTypeId:  (int) $data['payment_type_id'],
            couponCode:     isset($data['coupon_code']) && trim($data['coupon_code']) !== ''
                            ? trim($data['coupon_code'])
                            : null,
            notes:          isset($data['notes']) && trim($data['notes']) !== ''
                            ? trim($data['notes'])
                            : null,
        );
    }
}
