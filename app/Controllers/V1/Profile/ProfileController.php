<?php

declare(strict_types=1);

namespace App\Controllers\V1\Profile;

use App\Controllers\BaseController;
use App\Services\Profile\ProfileService;
use App\Validators\Profile\UpdateProfileValidator;
use App\DTOs\Profile\UpdateProfileDTO;
use Core\Request;
use Core\Response;

class ProfileController extends BaseController
{
    private ProfileService $profileService;

    public function __construct(Request $request, ProfileService $profileService)
    {
        parent::__construct($request);
        $this->profileService = $profileService;
    }

    public function show(Request $request): Response
    {
        $profile = $this->profileService->getProfile($this->userId());
        return $this->success($profile, 'Profile retrieved.');
    }

    public function update(Request $request): Response
    {
        $data    = (new UpdateProfileValidator())->validate($request->body());
        $dto     = UpdateProfileDTO::fromArray($data, $this->userId(), $request->file('profile_image'));
        $profile = $this->profileService->updateProfile($dto);
        return $this->updated($profile, 'Profile updated successfully.');
    }

    public function destroy(Request $request): Response
    {
        $this->profileService->deleteAccount($this->userId());
        return $this->success(null, 'Account deleted successfully.');
    }
}
