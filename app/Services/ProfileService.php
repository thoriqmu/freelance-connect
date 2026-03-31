<?php

namespace App\Services;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\FreelancerProfile;
use App\Models\Proposal;
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
        return User::with(['clientProfile', 'freelancerProfile'])
            ->withCount(['reviewsAsReviewee', 'projects as completed_projects_count' => function($query) {
                $query->where('status', 'completed');
            }])
            ->withAvg('reviewsAsReviewee', 'rating')
            ->find($userId);
    }

    public function getPublicProfile(int $userId): ?User
    {
        return User::with(['clientProfile', 'freelancerProfile'])
            ->withCount(['reviewsAsReviewee', 'projects as completed_projects_count' => function($query) {
                $query->where('status', 'completed');
            }])
            ->withAvg('reviewsAsReviewee', 'rating')
            ->select('id', 'name', 'email', 'role', 'avatar', 'created_at')
            ->find($userId);
    }

    /**
     * Check if a client can view a freelancer's profile
     */
    public function canViewFreelancerProfile(User $viewer, User $target): bool
    {
        // Clients can only view freelancers who have submitted a proposal or submission to their projects
        if ($viewer->role === 'client') {
            $clientProfile = $viewer->clientProfile;
            
            if (!$clientProfile) return false;
            
            $targetFreelancerProfile = $target->freelancerProfile;
            if (!$targetFreelancerProfile) return false;

            return Proposal::where('freelancer_id', $targetFreelancerProfile->id)
                ->whereHas('project', function($query) use ($clientProfile) {
                    $query->where('client_id', $clientProfile->id);
                })
                ->exists();
        }

        // Freelancers can view each other? For now, yes, or we can restrict.
        // But the user specificed "cegah client bypass".
        return true;
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
