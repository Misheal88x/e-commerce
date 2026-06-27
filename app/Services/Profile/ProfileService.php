<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Auth\TokenService;
use App\DTOs\Profile\UpdateProfileDTO;
use App\Helpers\FileUploadHelper;
use App\Helpers\HashHelper;
use App\Constants\Upload;
use App\Exceptions\NotFoundException;
use App\Exceptions\HttpException;

class ProfileService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepo,
        private readonly TokenService            $tokenService,
    ) {}

    public function getProfile(int $userId): array
    {
        $user = $this->userRepo->findById($userId);
        if (!$user) {
            throw new NotFoundException('User not found.');
        }
        return $user;
    }

    public function updateProfile(UpdateProfileDTO $dto): array
    {
        $data = array_filter([
            'fullname'  => $dto->fullname,
            'gender'    => $dto->gender,
            'birthdate' => $dto->birthdate,
        ], fn($v) => $v !== null);

        if ($dto->imageFile !== null) {
            $current = $this->userRepo->findById($dto->userId);
            FileUploadHelper::delete($current['profile_image'] ?? null);
            $data['profile_image'] = FileUploadHelper::upload($dto->imageFile, Upload::DIR_PROFILES);
        }

        if (!empty($data)) {
            $this->userRepo->update($dto->userId, $data);
        }

        return $this->userRepo->findById($dto->userId);
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): void
    {
        $user = $this->userRepo->findByEmail(
            $this->userRepo->findById($userId)['email']
        );

        if (!HashHelper::verify($currentPassword, $user['password'])) {
            throw new HttpException('Current password is incorrect.', 422);
        }

        $this->userRepo->update($userId, ['password' => HashHelper::make($newPassword)]);
        $this->tokenService->invalidateAll($userId);
    }

    public function deleteAccount(int $userId): void
    {
        $this->tokenService->invalidateAll($userId);
        $this->userRepo->softDelete($userId);
    }
}
