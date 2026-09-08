<?php

declare(strict_types=1);

namespace App\Services\DeliveryMethod;

use App\DTOs\DeliveryMethod\DeliveryMethodDTO;
use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\DeliveryMethodRepositoryInterface;

class DeliveryMethodService
{
    public function __construct(
        private readonly DeliveryMethodRepositoryInterface $deliveryMethodRepo
    ) {}

    public function getAll(): array
    {
        return $this->deliveryMethodRepo->findAll();
    }

    public function getAllActive(): array
    {
        return $this->deliveryMethodRepo->findActive();
    }

    public function getById(int $id): array
    {
        $deliveryMethod = $this->deliveryMethodRepo->findById($id);
        if (!$deliveryMethod) {
            throw new NotFoundException('Delivery method not found.');
        }
        return $deliveryMethod;
    }

    public function create(DeliveryMethodDTO $dto): array
    {
        $id = $this->deliveryMethodRepo->create([
            'title'                   => $dto->title,
            'description'             => $dto->description,
            'price'                   => $dto->price,
            'estimated_delivery_time' => $dto->estimatedDeliveryTime,
            'is_active'               => (int) $dto->isActive,
        ]);

        return $this->deliveryMethodRepo->findById($id);
    }

    public function update(int $id, array $validated): array
    {
        $deliveryMethod = $this->deliveryMethodRepo->findById($id);
        if (!$deliveryMethod) {
            throw new NotFoundException('Delivery method not found.');
        }

        $data = [];

        foreach (['title', 'description', 'price', 'estimated_delivery_time', 'is_active'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $data[$field] = $field === 'is_active'
                    ? (int) $validated[$field]
                    : $validated[$field];
            }
        }

        $this->deliveryMethodRepo->update($id, $data);

        return $this->deliveryMethodRepo->findById($id);
    }

    public function delete(int $id): void
    {
        $deliveryMethod = $this->deliveryMethodRepo->findById($id);
        if (!$deliveryMethod) {
            throw new NotFoundException('Delivery method not found.');
        }

        $this->deliveryMethodRepo->delete($id);
    }
}
