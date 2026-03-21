<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Requests\Project\UploadProjectAttachmentRequest;
use App\Services\ProjectService;
use App\Traits\ApiResponse;
use App\Models\Project;
use App\Models\ProjectAttachment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(private ProjectService $projectService) {}

    /**
     * List open projects for freelancers to browse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $projects = $this->projectService->getPaginatedProjects(
                filters: $request->only('search', 'min_budget', 'max_budget', 'sort_by', 'sort_order'),
                perPage: $request->input('per_page', 10)
            );

            return $this->successResponse('Success', $projects, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get project detail
     */
    public function show(int $id): JsonResponse
    {
        try {
            $project = $this->projectService->getProjectDetail($id);

            if (!$project) {
                return $this->notFoundResponse('Project not found');
            }

            return $this->successResponse('Success', $project, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Create a new project (client only)
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile) {
                return $this->errorResponse('Client profile not found', 404);
            }

            $project = $this->projectService->createProject($request->validated(), $clientProfile->id);

            return $this->successResponse('Project created successfully', $project, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Update project
     */
    public function update(int $id, UpdateProjectRequest $request): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile || $project->client_id !== $clientProfile->id) {
                return $this->forbiddenResponse();
            }

            $project = $this->projectService->updateProject($project, $request->validated());

            return $this->successResponse('Project updated successfully', $project, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Delete (archive) project
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile || $project->client_id !== $clientProfile->id) {
                return $this->forbiddenResponse();
            }

            $this->projectService->deleteProject($project);

            return $this->successResponse('Project archived successfully', null, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Upload project attachment
     */
    public function uploadAttachment(int $id, UploadProjectAttachmentRequest $request): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);

            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile || $project->client_id !== $clientProfile->id) {
                return $this->forbiddenResponse();
            }

            $attachment = $this->projectService->uploadAttachment(
                $request->file('file'),
                $id,
                $request->input('title')
            );

            return $this->successResponse('Attachment uploaded successfully', $attachment, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment(int $projectId, int $attachmentId): JsonResponse
    {
        try {
            $project = Project::findOrFail($projectId);
            $attachment = ProjectAttachment::findOrFail($attachmentId);

            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile || $project->client_id !== $clientProfile->id) {
                return $this->forbiddenResponse();
            }

            if ($attachment->project_id !== $projectId) {
                return $this->notFoundResponse('Attachment not found');
            }

            $this->projectService->deleteAttachment($attachment);

            return $this->successResponse('Attachment deleted successfully', null, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get my projects (client only)
     */
    public function myProjects(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile) {
                return $this->errorResponse('Client profile not found', 404);
            }

            $projects = $this->projectService->getUserProjects(
                $clientProfile->id,
                $request->input('per_page', 10)
            );

            return $this->successResponse('Success', $projects, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
