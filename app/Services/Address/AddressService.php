<?php

declare(strict_types=1);

namespace App\Services\Address;

use App\Repositories\Contracts\AddressRepositoryInterface;
use App\DTOs\Address\AddressDTO;
use App\Exceptions\NotFoundException;
use Core\Database\Connection;

class AddressService
{
    public function __construct(
        private readonly AddressRepositoryInterface $addressRepo
    ) {}

    public function getUserAddresses(int $userId): array
    {
        return $this->addressRepo->findByUserId($userId);
    }

    public function create(AddressDTO $dto): array
    {
        $db = Connection::getInstance();
        $db->beginTransaction();

        try {
            if ($dto->isDefault) {
                $this->addressRepo->unsetAllDefaults($dto->userId);
            }

            // First address is always default
            $isDefault = $dto->isDefault || $this->addressRepo->countByUserId($dto->userId) === 0;

            $id = $this->addressRepo->create([
                'user_id'         => $dto->userId,
                'latitude'        => $dto->latitude,
                'longitude'       => $dto->longitude,
                'city_name'       => $dto->cityName,
                'street_name'     => $dto->streetName,
                'building_number' => $dto->buildingNumber,
                'additional_info' => $dto->additionalInfo,
                'is_default'      => (int) $isDefault,
            ]);

            $db->commit();
            return $this->addressRepo->findById($id);
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function update(int $addressId, int $userId, AddressDTO $dto): array
    {
        $address = $this->addressRepo->findByIdAndUserId($addressId, $userId);
        if (!$address) {
            throw new NotFoundException('Address not found.');
        }

        $db = Connection::getInstance();
        $db->beginTransaction();

        try {
            if ($dto->isDefault) {
                $this->addressRepo->unsetAllDefaults($userId);
            }

            $this->addressRepo->update($addressId, [
                'latitude'        => $dto->latitude,
                'longitude'       => $dto->longitude,
                'city_name'       => $dto->cityName,
                'street_name'     => $dto->streetName,
                'building_number' => $dto->buildingNumber,
                'additional_info' => $dto->additionalInfo,
                'is_default'      => (int) $dto->isDefault,
            ]);

            $db->commit();
            return $this->addressRepo->findById($addressId);
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public function delete(int $addressId, int $userId): void
    {
        $address = $this->addressRepo->findByIdAndUserId($addressId, $userId);
        if (!$address) {
            throw new NotFoundException('Address not found.');
        }

        $this->addressRepo->softDelete($addressId);

        // Reassign default if needed
        if ($address['is_default']) {
            $next = $this->addressRepo->findNextAfterDelete($userId, $addressId);
            if ($next) {
                $this->addressRepo->setDefault((int) $next['id']);
            }
        }
    }

    public function setDefault(int $addressId, int $userId): array
    {
        $address = $this->addressRepo->findByIdAndUserId($addressId, $userId);
        if (!$address) {
            throw new NotFoundException('Address not found.');
        }

        $db = Connection::getInstance();
        $db->beginTransaction();
        try {
            $this->addressRepo->unsetAllDefaults($userId);
            $this->addressRepo->setDefault($addressId);
            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->addressRepo->findById($addressId);
    }
}
