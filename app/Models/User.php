<?php

declare(strict_types=1);

namespace App\Models;

class User
{
    public function __construct(
        public readonly int     $id,
        public readonly string  $fullname,
        public readonly string  $email,
        public readonly string  $gender,
        public readonly ?string $birthdate,
        public readonly ?string $profileImage,
        public readonly string  $createdAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id:           (int)    $data['id'],
            fullname:             $data['fullname'],
            email:                $data['email'],
            gender:               $data['gender'],
            birthdate:            $data['birthdate'] ?? null,
            profileImage:         $data['profile_image'] ?? null,
            createdAt:            $data['created_at'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'fullname'      => $this->fullname,
            'email'         => $this->email,
            'gender'        => $this->gender,
            'birthdate'     => $this->birthdate,
            'profile_image' => $this->profileImage,
            'created_at'    => $this->createdAt,
        ];
    }
}
