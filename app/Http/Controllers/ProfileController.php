<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\UpdateClientProfileRequest;
use App\Http\Requests\Profile\UpdateFreelancerProfileRequest;
use App\Services\ProfileService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(private ProfileService $profileService) {}

    /**
     * Get current user profile
     */
    public function show(): JsonResponse
    {
        try {
            $user = $this->profileService->getUserProfile(auth()->id());

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            return $this->successResponse('Success', $user, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * View public profile of another user
     */
    public function viewPublicProfile(int $userId): JsonResponse
    {
        try {
            $user = $this->profileService->getPublicProfile($userId);

            if (!$user) {
                return $this->notFoundResponse('User not found');
            }

            return $this->successResponse('Success', $user, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update shared profile (name, avatar)
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        try {
            $user = $this->profileService->updateProfile(auth()->user(), $request->validated());

            return $this->successResponse('Profile updated successfully', $user, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update client profile
     */
    public function updateClientProfile(UpdateClientProfileRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile) {
                return $this->notFoundResponse('Client profile not found');
            }

            $profile = $this->profileService->updateClientProfile($clientProfile, $request->validated());

            return $this->successResponse('Client profile updated successfully', $profile, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update freelancer profile
     */
    public function updateFreelancerProfile(UpdateFreelancerProfileRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $freelancerProfile = $user->freelancerProfile;

            if (!$freelancerProfile) {
                return $this->notFoundResponse('Freelancer profile not found');
            }

            $profile = $this->profileService->updateFreelancerProfile($freelancerProfile, $request->validated());

            return $this->successResponse('Freelancer profile updated successfully', $profile, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
