<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Proposal;
use App\Models\SavedJob;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    use ApiResponse;
    /**
     * Get freelancer dashboard statistics
     */
    public function freelancerStats(): JsonResponse
    {
        $user = auth()->user();
        $freelancerProfile = $user->freelancerProfile;

        if (!$freelancerProfile) {
            return $this->errorResponse('Freelancer profile not found', 404);
        }

        $availableJobs = Project::where('status', 'open')->count();
        
        $activeBids = Proposal::where('freelancer_id', $freelancerProfile->id)
            ->whereHas('project', function ($query) {
                $query->where('status', 'open');
            })
            ->count();
            
        $savedJobs = SavedJob::where('user_id', $user->id)->count();

        return $this->successResponse('Freelancer stats retrieved', [
            'available_jobs' => $availableJobs,
            'active_bids' => $activeBids,
            'saved_jobs' => $savedJobs
        ]);
    }

    /**
     * Get client dashboard statistics
     */
    public function clientStats(): JsonResponse
    {
        $user = auth()->user();
        $clientProfile = $user->clientProfile;

        if (!$clientProfile) {
            return $this->errorResponse('Client profile not found', 404);
        }

        $activeProjects = Project::where('client_id', $clientProfile->id)
            ->where('status', 'in_progress')
            ->count();
            
        $totalSpent = Project::where('client_id', $clientProfile->id)
            ->where('status', 'completed')
            ->sum('budget');
            
        $proposalsReceived = Proposal::whereHas('project', function ($query) use ($clientProfile) {
            $query->where('client_id', $clientProfile->id)
                  ->where('status', 'open');
        })->count();
        
        $completedProjects = Project::where('client_id', $clientProfile->id)
            ->where('status', 'completed')
            ->count();

        return $this->successResponse('Client stats retrieved', [
            'active_projects' => $activeProjects,
            'total_spent' => (float) $totalSpent,
            'proposals_received' => $proposalsReceived,
            'completed_projects' => $completedProjects
        ]);
    }
}
