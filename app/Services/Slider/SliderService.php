<?php

declare(strict_types=1);

namespace App\Services\Slider;

use App\Constants\Upload;
use App\DTOs\Slider\SliderDTO;
use App\Exceptions\NotFoundException;
use App\Exceptions\ValidationException;
use App\Helpers\FileUploadHelper;
use App\Repositories\Contracts\SliderRepositoryInterface;

class SliderService
{
    public function __construct(
        private readonly SliderRepositoryInterface $sliderRepo,
    ) {}

    public function getAll(): array
    {
        return $this->sliderRepo->findAll();
    }

    public function getById(int $id): array
    {
        $slider = $this->sliderRepo->findById($id);
        if (!$slider) {
            throw new NotFoundException('Slider not found.');
        }
        return $slider;
    }

    public function create(SliderDTO $dto, ?array $imageFile): array
    {
        if (!$imageFile || $imageFile['error'] !== UPLOAD_ERR_OK) {
            throw new ValidationException(['image' => ['The image field is required.']]);
        }

        $imagePath = FileUploadHelper::upload($imageFile, Upload::DIR_SLIDERS);

        $id = $this->sliderRepo->create([
            'title'          => $dto->title,
            'image'          => $imagePath,
            'redirect_type'  => $dto->redirectType,
            'redirect_value' => $dto->redirectValue,
            'is_active'      => (int) $dto->isActive,
            'sort_order'     => $dto->sortOrder,
        ]);

        return $this->sliderRepo->findById($id);
    }

    public function update(int $id, array $validated, ?array $imageFile): array
    {
        $slider = $this->sliderRepo->findById($id);
        if (!$slider) {
            throw new NotFoundException('Slider not found.');
        }

        $data = [];

        foreach (['title', 'redirect_type', 'redirect_value', 'is_active', 'sort_order'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $data[$field] = $field === 'is_active'
                    ? (int) $validated[$field]
                    : $validated[$field];
            }
        }

        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            $data['image'] = FileUploadHelper::upload($imageFile, Upload::DIR_SLIDERS);
        }

        $this->sliderRepo->update($id, $data);

        return $this->sliderRepo->findById($id);
    }

    public function delete(int $id): void
    {
        $slider = $this->sliderRepo->findById($id);
        if (!$slider) {
            throw new NotFoundException('Slider not found.');
        }

        $this->sliderRepo->delete($id);
    }
}
