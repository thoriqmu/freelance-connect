<?php

namespace App\Services;

use App\Models\Proposal;
use App\Models\Project;
use App\Enums\ProposalStatus;
use App\Enums\ProjectStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class ProposalService
{
    public function __construct(
        private NotificationService $notificationService,
        private PaymentService $paymentService
    ) {}

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
     * Get proposals for projects belonging to a client
     */
    public function getClientProposals(int $clientId, int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = Proposal::whereHas('project', function ($q) use ($clientId) {
            $q->where('client_id', $clientId);
        })->with(['freelancer.user', 'project']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereHas('project', function ($sq) use ($search) {
                    $sq->where('title', 'like', "%{$search}%");
                })->orWhereHas('freelancer.user', function ($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%");
                });
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
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

            // Notify Client
            $this->notificationService->send(
                $project->client->user->id,
                'new_proposal',
                "New proposal from {$proposal->freelancer->user->name} for '{$project->title}'",
                ['project_id' => $projectId, 'proposal_id' => $proposal->id]
            );

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
        return DB::transaction(function () use ($proposal) {
            try {
                $project = $proposal->project;

                // Reject all other proposals
                Proposal::where('project_id', $project->id)
                    ->where('id', '!=', $proposal->id)
                    ->update(['status' => ProposalStatus::REJECTED->value]);

                // Update proposal status
                $proposal->update(['status' => ProposalStatus::ACCEPTED->value]);

                // Update project status to WAITING_PAYMENT and assign freelancer
                $project->update([
                    'status' => ProjectStatus::WAITING_PAYMENT->value,
                    'freelancer_id' => $proposal->freelancer_id,
                ]);

                // Initiate Payment and Xendit Invoice
                $this->paymentService->initiateEscrowPayment($project, $proposal);

                // Notify Freelancer
                $this->notificationService->send(
                    $proposal->freelancer->user->id,
                    'proposal_accepted',
                    "Your proposal for '{$project->title}' has been accepted!",
                    ['project_id' => $project->id, 'proposal_id' => $proposal->id]
                );

                Log::info('Proposal accepted', ['proposal_id' => $proposal->id]);

                return $proposal;
            } catch (\Exception $e) {
                Log::error('Accept proposal failed', ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * Reject proposal
     */
    public function rejectProposal(Proposal $proposal): Proposal
    {
        try {
            $proposal->update(['status' => ProposalStatus::REJECTED->value]);

            // Notify Freelancer
            $this->notificationService->send(
                $proposal->freelancer->user->id,
                'proposal_rejected',
                "Your proposal for '{$proposal->project->title}' was rejected.",
                ['project_id' => $proposal->project_id, 'proposal_id' => $proposal->id]
            );

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

    /**
     * Check if freelancer already submitted a proposal for a project
     */
    public function checkExistingProposal(int $projectId, int $freelancerId): ?Proposal
    {
        return Proposal::where('project_id', $projectId)
            ->where('freelancer_id', $freelancerId)
            ->first();
    }
}
