<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectAttachment;
use App\Enums\ProjectStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjectService
{
    /**
     * Get paginated projects (OPEN status for freelancers to browse)
     */
    public function getPaginatedProjects(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Project::query()
            ->where('status', ProjectStatus::OPEN->value)
            ->with(['client.user', 'freelancer.user', 'projectAttachments']);

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['min_budget'])) {
            $query->where('budget', '>=', $filters['min_budget']);
        }

        if (!empty($filters['max_budget'])) {
            $query->where('budget', '<=', $filters['max_budget']);
        }

        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        $allowedSorts = ['created_at', 'budget', 'title'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
        
        return $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
    }

    /**
     * Get user's projects
     */
    public function getUserProjects(int $clientId, int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Project::where('client_id', $clientId)
            ->with(['client.user', 'freelancer.user', 'projectAttachments'])
            ->withCount('proposals');

        if (!empty($filters['search'])) {
            $query->where(function($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new project
     */
    public function createProject(array $data, int $clientId): Project
    {
        try {
            $project = Project::create([
                'client_id' => $clientId,
                'freelancer_id' => null,
                'title' => $data['title'],
                'description' => $data['description'],
                'budget' => $data['budget'],
                'timeline' => $data['timeline'],
                'status' => ProjectStatus::OPEN->value,
            ]);

            Log::info('Project created', ['project_id' => $project->id, 'client_id' => $clientId]);

            return $project;
        } catch (\Exception $e) {
            Log::error('Create project failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Update project
     */
    public function updateProject(Project $project, array $data): Project
    {
        try {
            if (!in_array($project->status, [ProjectStatus::OPEN->value, ProjectStatus::ARCHIVED->value])) {
                throw new \Exception('Project can only be edited when status is OPEN or ARCHIVED.', 403);
            }

            $project->update($data);

            Log::info('Project updated', ['project_id' => $project->id]);

            return $project;
        } catch (\Exception $e) {
            Log::error('Update project failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete (hard delete) project
     */
    public function deleteProject(Project $project): bool
    {
        try {
            if (!in_array($project->status, [ProjectStatus::OPEN->value, ProjectStatus::ARCHIVED->value])) {
                throw new \Exception('Project can only be deleted when status is OPEN or ARCHIVED.', 403);
            }

            if ($project->freelancer_id !== null) {
                throw new \Exception('Cannot delete a project that already has a hired freelancer.', 403);
            }

            $project->delete();

            Log::info('Project deleted (hard)', ['project_id' => $project->id]);

            return true;
        } catch (\Exception $e) {
            Log::error('Delete project failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Archive project
     */
    public function archiveProject(Project $project): Project
    {
        try {
            if ($project->status !== ProjectStatus::OPEN->value) {
                throw new \Exception('Only OPEN projects can be archived.', 403);
            }
            if ($project->proposals()->count() > 0) {
                throw new \Exception('Cannot archive a project that already has proposals received.', 403);
            }

            $project->update(['status' => ProjectStatus::ARCHIVED->value]);

            Log::info('Project archived', ['project_id' => $project->id]);

            return $project;
        } catch (\Exception $e) {
            Log::error('Archive project failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Unarchive project
     */
    public function unarchiveProject(Project $project): Project
    {
        try {
            if ($project->status !== ProjectStatus::ARCHIVED->value) {
                throw new \Exception('Only ARCHIVED projects can be unarchived.', 403);
            }

            $project->update(['status' => ProjectStatus::OPEN->value]);

            Log::info('Project unarchived', ['project_id' => $project->id]);

            return $project;
        } catch (\Exception $e) {
            Log::error('Unarchive project failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Upload project attachment
     */
    public function uploadAttachment(UploadedFile $file, int $projectId, string $title = null): ProjectAttachment
    {
        try {
            $path = $file->store("projects/{$projectId}/attachments", 'public');

            $attachment = ProjectAttachment::create([
                'project_id' => $projectId,
                'title' => $title ?? $file->getClientOriginalName(),
                'file_path' => $path,
            ]);

            Log::info('Attachment uploaded', ['attachment_id' => $attachment->id, 'project_id' => $projectId]);

            return $attachment;
        } catch (\Exception $e) {
            Log::error('Upload attachment failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment(ProjectAttachment $attachment): bool
    {
        try {
            Storage::disk('public')->delete($attachment->file_path);
            $attachment->delete();

            Log::info('Attachment deleted', ['attachment_id' => $attachment->id]);

            return true;
        } catch (\Exception $e) {
            Log::error('Delete attachment failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get project detail
     */
    public function getProjectDetail(int $projectId): ?Project
    {
        return Project::with([
            'client.user',
            'freelancer.user',
            'projectAttachments',
            'proposals',
            'reviews',
        ])->find($projectId);
    }
}
