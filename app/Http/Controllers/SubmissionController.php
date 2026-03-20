<?php

namespace App\Http\Controllers;

use App\Http\Requests\Submission\StoreSubmissionRequest;
use App\Services\SubmissionService;
use App\Traits\ApiResponse;
use App\Models\Submission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    use ApiResponse;

    public function __construct(private SubmissionService $submissionService) {}

    /**
     * Get submissions for a project
     */
    public function projectSubmissions(int $projectId, Request $request): JsonResponse
    {
        try {
            $project = \App\Models\Project::findOrFail($projectId);
            $user = auth()->user();

            // Check authorization - client or assigned freelancer
            $isAuthorized = $project->client_id === $user->clientProfile?->id ||
                           $project->freelancer_id === $user->freelancerProfile?->id;

            if (!$isAuthorized) {
                return $this->forbiddenResponse();
            }

            $submissions = $this->submissionService->getProjectSubmissions(
                $projectId,
                $request->input('per_page', 10)
            );

            return $this->successResponse('Success', $submissions, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get submission detail
     */
    public function show(int $id): JsonResponse
    {
        try {
            $submission = $this->submissionService->getSubmissionDetail($id);

            if (!$submission) {
                return $this->notFoundResponse('Submission not found');
            }

            return $this->successResponse('Success', $submission, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Submit work (freelancer only)
     */
    public function store(int $projectId, StoreSubmissionRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $freelancerProfile = $user->freelancerProfile;

            if (!$freelancerProfile) {
                return $this->errorResponse('Freelancer profile not found', 404);
            }

            $submission = $this->submissionService->submitWork(
                $request->validated(),
                $projectId,
                $freelancerProfile->id
            );

            return $this->successResponse('Submission created successfully', $submission, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Approve submission (client only)
     */
    public function approve(int $id): JsonResponse
    {
        try {
            $submission = Submission::findOrFail($id);
            $project = $submission->project;

            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile || $project->client_id !== $clientProfile->id) {
                return $this->forbiddenResponse();
            }

            $submission = $this->submissionService->approveSubmission($submission);

            return $this->successResponse('Submission approved successfully', $submission, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Request revision (client only)
     */
    public function requestRevision(int $id): JsonResponse
    {
        try {
            $submission = Submission::findOrFail($id);
            $project = $submission->project;

            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile || $project->client_id !== $clientProfile->id) {
                return $this->forbiddenResponse();
            }

            $submission = $this->submissionService->requestRevision($submission);

            return $this->successResponse('Revision requested successfully', $submission, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
