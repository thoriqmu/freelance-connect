<?php

namespace App\Http\Controllers;

use App\Http\Requests\SavedJob\SaveJobRequest;
use App\Services\SavedJobService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedJobController extends Controller
{
    use ApiResponse;

    public function __construct(private SavedJobService $savedJobService) {}

    /**
     * Get all saved jobs for freelancer
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $savedJobs = $this->savedJobService->getSavedJobs(
                auth()->id(),
                $request->input('per_page', 10)
            );

            return $this->successResponse('Success', $savedJobs, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Save a job
     */
    public function store(SaveJobRequest $request): JsonResponse
    {
        try {
            $savedJob = $this->savedJobService->saveJob(
                auth()->id(),
                $request->input('project_id')
            );

            return $this->successResponse('Job saved successfully', $savedJob, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Check if a job is saved
     */
    public function isSaved(int $projectId): JsonResponse
    {
        try {
            $isSaved = $this->savedJobService->isSaved(auth()->id(), $projectId);

            return $this->successResponse('Success', ['saved' => $isSaved], 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete saved job
     */
    public function destroy(int $projectId): JsonResponse
    {
        try {
            $this->savedJobService->deleteSavedJob(auth()->id(), $projectId);

            return $this->successResponse('Saved job removed successfully', null, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }
}
