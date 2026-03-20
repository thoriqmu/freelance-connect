<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\Project;
use App\Enums\ProposalStatus;
use App\Enums\ProjectStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class ProposalService
{
    /**
     * Get proposals for a project
     */
    public function getProjectProposals(int $projectId, int $perPage = 10): LengthAwarePaginator
    {
        return Proposal::where('project_id', $projectId)
            ->with(['freelancer.user', 'project'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get freelancer's proposals
     */
    public function getFreelancerProposals(int $freelancerId, int $perPage = 10): LengthAwarePaginator
    {
        return Proposal::where('freelancer_id', $freelancerId)
            ->with(['project.client.user', 'freelancer.user'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Submit a proposal
     */
    public function submitProposal(array $data, int $projectId, int $freelancerId): Proposal
    {
        try {
            $project = Project::findOrFail($projectId);

            if ($project->status !== ProjectStatus::OPEN->value) {
                throw new \Exception('Project is not open for proposals.', 403);
            }

            $alreadyApplied = Proposal::where('project_id', $projectId)
                ->where('freelancer_id', $freelancerId)
                ->exists();

            if ($alreadyApplied) {
                throw new \Exception('You have already submitted a proposal for this project.', 422);
            }

            $proposal = Proposal::create([
                'project_id' => $projectId,
                'freelancer_id' => $freelancerId,
                'bid_price' => $data['bid_price'],
                'estimated_time' => $data['estimated_time'],
                'message' => $data['message'],
                'status' => ProposalStatus::PENDING->value,
            ]);

            Log::info('Proposal submitted', ['proposal_id' => $proposal->id, 'project_id' => $projectId]);

            return $proposal;
        } catch (\Exception $e) {
            Log::error('Submit proposal failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Accept proposal
     */
    public function acceptProposal(Proposal $proposal): Proposal
    {
        try {
            $project = $proposal->project;

            // Reject all other proposals
            Proposal::where('project_id', $project->id)
                ->where('id', '!=', $proposal->id)
                ->update(['status' => ProposalStatus::REJECTED->value]);

            // Update proposal status
            $proposal->update(['status' => ProposalStatus::ACCEPTED->value]);

            // Update project status to IN_PROGRESS and assign freelancer
            $project->update([
                'status' => ProjectStatus::IN_PROGRESS->value,
                'freelancer_id' => $proposal->freelancer_id,
            ]);

            Log::info('Proposal accepted', ['proposal_id' => $proposal->id]);

            return $proposal;
        } catch (\Exception $e) {
            Log::error('Accept proposal failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Reject proposal
     */
    public function rejectProposal(Proposal $proposal): Proposal
    {
        try {
            $proposal->update(['status' => ProposalStatus::REJECTED->value]);

            Log::info('Proposal rejected', ['proposal_id' => $proposal->id]);

            return $proposal;
        } catch (\Exception $e) {
            Log::error('Reject proposal failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get proposal detail
     */
    public function getProposalDetail(int $proposalId): ?Proposal
    {
        return Proposal::with(['freelancer.user', 'project.client.user'])->find($proposalId);
    }
}
