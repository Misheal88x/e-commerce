<?php

declare(strict_types=1);

namespace App\Models;

class Address
{
    public function __construct(
        public readonly int     $id,
        public readonly int     $userId,
        public readonly ?float  $latitude,
        public readonly ?float  $longitude,
        public readonly string  $cityName,
        public readonly string  $streetName,
        public readonly ?string $buildingNumber,
        public readonly ?string $additionalInfo,
        public readonly bool    $isDefault,
        public readonly string  $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:             (int)   $data['id'],
            userId:         (int)   $data['user_id'],
            latitude:       isset($data['latitude'])  ? (float) $data['latitude']  : null,
            longitude:      isset($data['longitude']) ? (float) $data['longitude'] : null,
            cityName:               $data['city_name'],
            streetName:             $data['street_name'],
            buildingNumber:         $data['building_number'] ?? null,
            additionalInfo:         $data['additional_info'] ?? null,
            isDefault:      (bool)  $data['is_default'],
            createdAt:              $data['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'user_id'         => $this->userId,
            'latitude'        => $this->latitude,
            'longitude'       => $this->longitude,
            'city_name'       => $this->cityName,
            'street_name'     => $this->streetName,
            'building_number' => $this->buildingNumber,
            'additional_info' => $this->additionalInfo,
            'is_default'      => $this->isDefault,
            'created_at'      => $this->createdAt,
        ];
    }
}
