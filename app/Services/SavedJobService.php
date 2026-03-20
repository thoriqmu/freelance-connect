<?php

namespace App\Services;

use App\Models\SavedJob;
use App\Models\Project;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class SavedJobService
{
    /**
     * Get freelancer's saved jobs
     */
    public function getSavedJobs(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return SavedJob::where('user_id', $userId)
            ->with(['project.client.user', 'project.projectAttachments'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Save a job
     */
    public function saveJob(int $userId, int $projectId): SavedJob
    {
        try {
            $project = Project::findOrFail($projectId);

            // Check if already saved
            $alreadySaved = SavedJob::where('user_id', $userId)
                ->where('project_id', $projectId)
                ->exists();

            if ($alreadySaved) {
                throw new \Exception('You have already saved this job.', 422);
            }

            // Check if project is open
            if ($project->status !== 'open') {
                throw new \Exception('You can only save open projects.', 403);
            }

            $savedJob = SavedJob::create([
                'user_id' => $userId,
                'project_id' => $projectId,
            ]);

            Log::info('Job saved', ['saved_job_id' => $savedJob->id, 'user_id' => $userId]);

            return $savedJob;
        } catch (\Exception $e) {
            Log::error('Save job failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete saved job
     */
    public function deleteSavedJob(int $userId, int $projectId): bool
    {
        try {
            $deleted = SavedJob::where('user_id', $userId)
                ->where('project_id', $projectId)
                ->delete();

            if ($deleted) {
                Log::info('Saved job deleted', ['user_id' => $userId, 'project_id' => $projectId]);
                return true;
            }

            throw new \Exception('Saved job not found.', 404);
        } catch (\Exception $e) {
            Log::error('Delete saved job failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Check if job is saved
     */
    public function isSaved(int $userId, int $projectId): bool
    {
        return SavedJob::where('user_id', $userId)
            ->where('project_id', $projectId)
            ->exists();
    }
}
