<?php

declare(strict_types=1);

namespace App\Services\Brand;

use App\Constants\Upload;
use App\DTOs\Brand\BrandDTO;
use App\Exceptions\NotFoundException;
use App\Helpers\FileUploadHelper;
use App\Repositories\Contracts\BrandRepositoryInterface;

class BrandService
{
    public function __construct(
        private readonly BrandRepositoryInterface $brandRepo
    ) {}

    public function getAllActive(): array
    {
        return $this->brandRepo->findActive();
    }

    public function getById(int $id): array
    {
        $brand = $this->brandRepo->findById($id);
        if (!$brand) {
            throw new NotFoundException('Brand not found.');
        }
        return $brand;
    }

    public function create(BrandDTO $dto, ?array $iconFile, ?array $imageFile): array
    {
        $data = [
            'name'      => $dto->name,
            'is_active' => (int) $dto->isActive,
        ];

        if ($iconFile && $iconFile['error'] === UPLOAD_ERR_OK) {
            $data['icon'] = FileUploadHelper::upload($iconFile, Upload::DIR_BRANDS);
        }

        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $data['image'] = FileUploadHelper::upload($imageFile, Upload::DIR_BRANDS);
        }

        $id = $this->brandRepo->create($data);

        return $this->brandRepo->findById($id);
    }

    public function update(int $id, array $validated, ?array $iconFile, ?array $imageFile): array
    {
        $brand = $this->brandRepo->findById($id);
        if (!$brand) {
            throw new NotFoundException('Brand not found.');
        }

        $data = [];

        foreach (['name', 'is_active'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $data[$field] = $field === 'is_active'
                    ? (int) $validated[$field]
                    : $validated[$field];
            }
        }

        if ($iconFile && $iconFile['error'] === UPLOAD_ERR_OK) {
            $data['icon'] = FileUploadHelper::upload($iconFile, Upload::DIR_BRANDS);
        }

        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $data['image'] = FileUploadHelper::upload($imageFile, Upload::DIR_BRANDS);
        }

        $this->brandRepo->update($id, $data);

        return $this->brandRepo->findById($id);
    }

    public function delete(int $id): void
    {
        $brand = $this->brandRepo->findById($id);
        if (!$brand) {
            throw new NotFoundException('Brand not found.');
        }

        $this->brandRepo->delete($id);
    }
}
