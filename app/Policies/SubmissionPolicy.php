<?php

namespace App\Policies;

use App\Models\Submission;
use App\Models\User;

class SubmissionPolicy
{
    /**
     * Determine if user can view the submission.
     */
    public function view(User $user, Submission $submission): bool
    {
        return $user->clientProfile?->id === $submission->project->client_id ||
               $user->freelancerProfile?->id === $submission->freelancer_id;
    }

    /**
     * Determine if user can approve the submission (client only).
     */
    public function approve(User $user, Submission $submission): bool
    {
        return $user->clientProfile?->id === $submission->project->client_id;
    }

    /**
     * Determine if user can request revision (client only).
     */
    public function requestRevision(User $user, Submission $submission): bool
    {
        return $user->clientProfile?->id === $submission->project->client_id;
    }
}
