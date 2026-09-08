<?php

declare(strict_types=1);

namespace App\Services\PaymentType;

use App\DTOs\PaymentType\PaymentTypeDTO;
use App\Exceptions\NotFoundException;
use App\Repositories\Contracts\PaymentTypeRepositoryInterface;

class PaymentTypeService
{
    public function __construct(
        private readonly PaymentTypeRepositoryInterface $paymentTypeRepo
    ) {}

    public function getAll(): array
    {
        return $this->paymentTypeRepo->findAll();
    }

    public function getAllActive(): array
    {
        return $this->paymentTypeRepo->findActive();
    }

    public function getById(int $id): array
    {
        $paymentType = $this->paymentTypeRepo->findById($id);
        if (!$paymentType) {
            throw new NotFoundException('Payment type not found.');
        }
        return $paymentType;
    }

    public function create(PaymentTypeDTO $dto): array
    {
        $id = $this->paymentTypeRepo->create([
            'name'      => $dto->name,
            'is_active' => (int) $dto->isActive,
        ]);

        return $this->paymentTypeRepo->findById($id);
    }

    public function update(int $id, array $validated): array
    {
        $paymentType = $this->paymentTypeRepo->findById($id);
        if (!$paymentType) {
            throw new NotFoundException('Payment type not found.');
        }

        $data = [];

        foreach (['name', 'is_active'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $data[$field] = $field === 'is_active'
                    ? (int) $validated[$field]
                    : $validated[$field];
            }
        }

        $this->paymentTypeRepo->update($id, $data);

        return $this->paymentTypeRepo->findById($id);
    }

    public function delete(int $id): void
    {
        $paymentType = $this->paymentTypeRepo->findById($id);
        if (!$paymentType) {
            throw new NotFoundException('Payment type not found.');
        }

        $this->paymentTypeRepo->delete($id);
    }
}
