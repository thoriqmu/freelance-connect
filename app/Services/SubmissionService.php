<?php

namespace App\Services;

use App\Models\Submission;
use App\Models\SubmissionAttachment;
use App\Models\Project;
use App\Enums\SubmissionStatus;
use App\Enums\ProjectStatus;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SubmissionService
{
    /**
     * Get submissions for a project
     */
    public function getProjectSubmissions(int $projectId, int $perPage = 10): LengthAwarePaginator
    {
        return Submission::where('project_id', $projectId)
            ->with(['freelancer.user', 'project', 'submissionAttachments'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Submit project work
     */
    public function submitWork(array $data, int $projectId, int $freelancerId): Submission
    {
        try {
            $project = Project::findOrFail($projectId);

            if ($project->status !== ProjectStatus::IN_PROGRESS->value) {
                throw new \Exception('Submission can only be created for IN_PROGRESS projects.', 403);
            }

            if ($project->freelancer_id !== $freelancerId) {
                throw new \Exception('Only the assigned freelancer can submit work.', 403);
            }

            $submission = Submission::create([
                'project_id' => $projectId,
                'freelancer_id' => $freelancerId,
                'note' => $data['note'],
                'status' => SubmissionStatus::PENDING->value,
            ]);

            // Handle attachments if provided
            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $this->uploadAttachment($file, $submission->id);
                }
            }

            Log::info('Submission created', ['submission_id' => $submission->id, 'project_id' => $projectId]);

            return $submission;
        } catch (\Exception $e) {
            Log::error('Submit work failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Upload submission attachment
     */
    public function uploadAttachment(UploadedFile $file, int $submissionId): SubmissionAttachment
    {
        try {
            $path = $file->store("submissions/{$submissionId}/attachments", 'public');

            $attachment = SubmissionAttachment::create([
                'submission_id' => $submissionId,
                'title' => $file->getClientOriginalName(),
                'file_path' => $path,
            ]);

            return $attachment;
        } catch (\Exception $e) {
            Log::error('Upload submission attachment failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Approve submission
     */
    public function approveSubmission(Submission $submission): Submission
    {
        try {
            $submission->update(['status' => SubmissionStatus::ACCEPTED->value]);

            // Mark project as COMPLETED
            $submission->project->update(['status' => ProjectStatus::COMPLETED->value]);

            Log::info('Submission approved', ['submission_id' => $submission->id]);

            return $submission;
        } catch (\Exception $e) {
            Log::error('Approve submission failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Request revision
     */
    public function requestRevision(Submission $submission): Submission
    {
        try {
            $submission->update(['status' => SubmissionStatus::REJECTED->value]);

            Log::info('Revision requested', ['submission_id' => $submission->id]);

            return $submission;
        } catch (\Exception $e) {
            Log::error('Request revision failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get submission detail
     */
    public function getSubmissionDetail(int $submissionId): ?Submission
    {
        return Submission::with(['freelancer.user', 'project.client.user', 'submissionAttachments'])->find($submissionId);
    }
}
