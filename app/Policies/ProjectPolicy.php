<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine if user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        return true; // All authenticated users can view projects
    }

    /**
     * Determine if user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->clientProfile?->id === $project->client_id && $project->status === 'open';
    }

    /**
     * Determine if user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $user->clientProfile?->id === $project->client_id && $project->status === 'open';
    }

    /**
     * Determine if user can view proposals for the project.
     */
    public function viewProposals(User $user, Project $project): bool
    {
        return $user->clientProfile?->id === $project->client_id;
    }

    /**
     * Determine if user can upload attachments to the project.
     */
    public function uploadAttachments(User $user, Project $project): bool
    {
        return $user->clientProfile?->id === $project->client_id;
    }
}
