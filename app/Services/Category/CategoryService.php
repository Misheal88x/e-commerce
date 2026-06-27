<?php

declare(strict_types=1);

namespace App\Services\Category;

use App\Constants\Upload;
use App\DTOs\Category\CategoryDTO;
use App\Exceptions\NotFoundException;
use App\Helpers\FileUploadHelper;
use App\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryService
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categoryRepo
    ) {}

    public function getAllActive(): array
    {
        return $this->categoryRepo->findActive();
    }

    public function getById(int $id): array
    {
        $category = $this->categoryRepo->findById($id);
        if (!$category) {
            throw new NotFoundException('Category not found.');
        }
        return $category;
    }

    public function create(CategoryDTO $dto, ?array $iconFile, ?array $imageFile): array
    {
        $data = [
            'name'      => $dto->name,
            'is_active' => (int) $dto->isActive,
        ];

        if ($iconFile && $iconFile['error'] === UPLOAD_ERR_OK) {
            $data['icon'] = FileUploadHelper::upload($iconFile, Upload::DIR_CATEGORIES);
        }

        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $data['image'] = FileUploadHelper::upload($imageFile, Upload::DIR_CATEGORIES);
        }

        $id = $this->categoryRepo->create($data);

        return $this->categoryRepo->findById($id);
    }

    public function update(int $id, array $validated, ?array $iconFile, ?array $imageFile): array
    {
        $category = $this->categoryRepo->findById($id);
        if (!$category) {
            throw new NotFoundException('Category not found.');
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
            $data['icon'] = FileUploadHelper::upload($iconFile, Upload::DIR_CATEGORIES);
        }

        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $data['image'] = FileUploadHelper::upload($imageFile, Upload::DIR_CATEGORIES);
        }

        $this->categoryRepo->update($id, $data);

        return $this->categoryRepo->findById($id);
    }

    public function delete(int $id): void
    {
        $category = $this->categoryRepo->findById($id);
        if (!$category) {
            throw new NotFoundException('Category not found.');
        }

        $this->categoryRepo->delete($id);
    }
}
