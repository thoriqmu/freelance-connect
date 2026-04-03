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
use Illuminate\Http\UploadedFile;

class SubmissionService
{
    public function __construct(
        private NotificationService $notificationService,
        private PaymentService $paymentService
    ) {}

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
                foreach ($data['attachments'] as $item) {
                    if (isset($item['file'])) {
                        $this->uploadAttachment($item['file'], $submission->id, $item['title'] ?? null);
                    }
                }
            }

            // Notify Client
            $this->notificationService->send(
                $project->client->user->id,
                'new_submission',
                "Freelancer {$submission->freelancer->user->name} has submitted work for '{$project->title}'",
                ['project_id' => $projectId, 'submission_id' => $submission->id]
            );

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
    public function uploadAttachment(UploadedFile $file, int $submissionId, ?string $title = null): SubmissionAttachment
    {
        try {
            $path = $file->store("submissions/{$submissionId}/attachments", 'public');

            $attachment = SubmissionAttachment::create([
                'submission_id' => $submissionId,
                'title' => $title ?? $file->getClientOriginalName(),
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
        return \Illuminate\Support\Facades\DB::transaction(function () use ($submission) {
            try {
                $submission->update(['status' => SubmissionStatus::ACCEPTED->value]);

                // Mark project as COMPLETED
                $submission->project->update(['status' => ProjectStatus::COMPLETED->value]);

                // Release payment from escrow
                $this->paymentService->releaseEscrowPayment($submission->project);

                // Notify Freelancer (Approved)
                $this->notificationService->send(
                    $submission->freelancer->user->id,
                    'submission_approved',
                    "Your submission for '{$submission->project->title}' has been approved!",
                    ['project_id' => $submission->project_id, 'submission_id' => $submission->id]
                );

                // Notify Freelancer (Project Completed)
                $this->notificationService->send(
                    $submission->freelancer->user->id,
                    'project_completed',
                    "Project '{$submission->project->title}' is now completed.",
                    ['project_id' => $submission->project_id]
                );

                Log::info('Submission approved and payout initiated', ['submission_id' => $submission->id]);

                return $submission;
            } catch (\Exception $e) {
                Log::error('Approve submission failed', ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * Request revision
     */
    public function requestRevision(Submission $submission, string $feedback): Submission
    {
        try {
            $submission->update(['status' => SubmissionStatus::REJECTED->value, 'feedback' => $feedback]);

            // Notify Freelancer
            $this->notificationService->send(
                $submission->freelancer->user->id,
                'revision_requested',
                "Revision requested for '{$submission->project->title}'",
                ['project_id' => $submission->project_id, 'submission_id' => $submission->id, 'feedback' => $feedback]
            );

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
