<?php

namespace App\Services;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\FreelancerProfile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProfileService
{
    /**
     * Get user profile
     */
    public function getUserProfile(int $userId): ?User
    {
        return User::with(['clientProfile', 'freelancerProfile'])->find($userId);
    }

    /**
     * Get public profile of a user
     */
    public function getPublicProfile(int $userId): ?User
    {
        return User::with(['clientProfile', 'freelancerProfile'])
            ->select('id', 'name', 'email', 'role', 'avatar', 'created_at')
            ->find($userId);
    }

    /**
     * Update shared profile
     */
    public function updateProfile(User $user, array $data): User
    {
        try {
            if (isset($data['name'])) {
                $user->update(['name' => $data['name']]);
            }

            if (isset($data['avatar'])) {
                // Delete old avatar if exists
                if ($user->avatar) {
                    Storage::disk('public')->delete($user->avatar);
                }

                $path = $data['avatar']->store('avatars', 'public');
                $user->update(['avatar' => $path]);
            }

            Log::info('Profile updated', ['user_id' => $user->id]);

            return $user;
        } catch (\Exception $e) {
            Log::error('Update profile failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update client profile
     */
    public function updateClientProfile(ClientProfile $profile, array $data): ClientProfile
    {
        try {
            $profile->update($data);

            Log::info('Client profile updated', ['client_profile_id' => $profile->id]);

            return $profile;
        } catch (\Exception $e) {
            Log::error('Update client profile failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update freelancer profile
     */
    public function updateFreelancerProfile(FreelancerProfile $profile, array $data): FreelancerProfile
    {
        try {
            $profile->update($data);

            Log::info('Freelancer profile updated', ['freelancer_profile_id' => $profile->id]);

            return $profile;
        } catch (\Exception $e) {
            Log::error('Update freelancer profile failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
