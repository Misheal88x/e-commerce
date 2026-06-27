<?php

declare(strict_types=1);

namespace App\DTOs\Address;

final class AddressDTO
{
    public function __construct(
        public readonly int     $userId,
        public readonly string  $cityName,
        public readonly string  $streetName,
        public readonly ?float  $latitude       = null,
        public readonly ?float  $longitude      = null,
        public readonly ?string $buildingNumber = null,
        public readonly ?string $additionalInfo = null,
        public readonly bool    $isDefault      = false,
    ) {}

    public static function fromArray(array $data, int $userId): static
    {
        return new static(
            userId:         $userId,
            cityName:       $data['city_name'],
            streetName:     $data['street_name'],
            latitude:       isset($data['latitude'])  ? (float) $data['latitude']  : null,
            longitude:      isset($data['longitude']) ? (float) $data['longitude'] : null,
            buildingNumber: $data['building_number']  ?? null,
            additionalInfo: $data['additional_info']  ?? null,
            isDefault:      (bool) ($data['is_default'] ?? false),
        );
    }
}
