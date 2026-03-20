<?php

namespace App\Policies;

use App\Models\Proposal;
use App\Models\User;

class ProposalPolicy
{
    /**
     * Determine if user can view the proposal.
     */
    public function view(User $user, Proposal $proposal): bool
    {
        return $user->clientProfile?->id === $proposal->project->client_id ||
               $user->freelancerProfile?->id === $proposal->freelancer_id;
    }

    /**
     * Determine if user can accept the proposal (client only).
     */
    public function accept(User $user, Proposal $proposal): bool
    {
        return $user->clientProfile?->id === $proposal->project->client_id;
    }

    /**
     * Determine if user can reject the proposal (client only).
     */
    public function reject(User $user, Proposal $proposal): bool
    {
        return $user->clientProfile?->id === $proposal->project->client_id;
    }
}
