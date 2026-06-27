<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;

class Order
{
    public function __construct(
        public readonly int          $id,
        public readonly int          $userId,
        public readonly OrderStatus  $status,
        public readonly float        $subtotal,
        public readonly float        $deliveryFee,
        public readonly float        $discountAmount,
        public readonly float        $totalPrice,
        public readonly ?string      $paymentName,
        public readonly ?string      $deliveryName,
        public readonly ?string      $notes,
        public readonly string       $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:             (int)   $data['id'],
            userId:         (int)   $data['user_id'],
            status:                 OrderStatus::from($data['status']),
            subtotal:       (float) $data['subtotal'],
            deliveryFee:    (float) $data['delivery_fee'],
            discountAmount: (float) ($data['discount_amount'] ?? 0),
            totalPrice:     (float) $data['total_price'],
            paymentName:            $data['payment_name']  ?? null,
            deliveryName:           $data['delivery_name'] ?? null,
            notes:                  $data['notes']         ?? null,
            createdAt:              $data['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->userId,
            'status'          => $this->status->value,
            'subtotal'        => $this->subtotal,
            'delivery_fee'    => $this->deliveryFee,
            'discount_amount' => $this->discountAmount,
            'total_price'     => $this->totalPrice,
            'payment_name'    => $this->paymentName,
            'delivery_name'   => $this->deliveryName,
            'notes'           => $this->notes,
            'created_at'      => $this->createdAt,
        ];
    }
}
