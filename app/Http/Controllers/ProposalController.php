<?php

namespace App\Http\Controllers;

use App\Http\Requests\Proposal\StoreProposalRequest;
use App\Services\ProposalService;
use App\Traits\ApiResponse;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    use ApiResponse;

    public function __construct(private ProposalService $proposalService) {}

    /**
     * Get proposals for a specific project (client only)
     */
    public function projectProposals(int $projectId, Request $request): JsonResponse
    {
        try {
            // Check authorization - only project client can see proposals
            $project = \App\Models\Project::findOrFail($projectId);
            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile || $project->client_id !== $clientProfile->id) {
                return $this->forbiddenResponse();
            }

            $proposals = $this->proposalService->getProjectProposals(
                $projectId,
                $request->input('per_page', 10)
            );

            return $this->successResponse('Success', $proposals, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get my proposals (freelancer only)
     */
    public function myProposals(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $freelancerProfile = $user->freelancerProfile;

            if (!$freelancerProfile) {
                return $this->errorResponse('Freelancer profile not found', 404);
            }

            $proposals = $this->proposalService->getFreelancerProposals(
                $freelancerProfile->id,
                $request->input('per_page', 10)
            );

            return $this->successResponse('Success', $proposals, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get proposal detail
     */
    public function show(int $id): JsonResponse
    {
        try {
            $proposal = $this->proposalService->getProposalDetail($id);

            if (!$proposal) {
                return $this->notFoundResponse('Proposal not found');
            }

            return $this->successResponse('Success', $proposal, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Submit proposal (freelancer only)
     */
    public function store(int $projectId, StoreProposalRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $freelancerProfile = $user->freelancerProfile;

            if (!$freelancerProfile) {
                return $this->errorResponse('Freelancer profile not found', 404);
            }

            $proposal = $this->proposalService->submitProposal(
                $request->validated(),
                $projectId,
                $freelancerProfile->id
            );

            return $this->successResponse('Proposal submitted successfully', $proposal, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Accept proposal (client only)
     */
    public function accept(int $projectId, int $proposalId): JsonResponse
    {
        try {
            $proposal = Proposal::findOrFail($proposalId);
            $project = $proposal->project;

            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile || $project->client_id !== $clientProfile->id) {
                return $this->forbiddenResponse();
            }

            $proposal = $this->proposalService->acceptProposal($proposal);

            return $this->successResponse('Proposal accepted successfully', $proposal, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Reject proposal (client only)
     */
    public function reject(int $projectId, int $proposalId): JsonResponse
    {
        try {
            $proposal = Proposal::findOrFail($proposalId);
            $project = $proposal->project;

            $user = auth()->user();
            $clientProfile = $user->clientProfile;

            if (!$clientProfile || $project->client_id !== $clientProfile->id) {
                return $this->forbiddenResponse();
            }

            $proposal = $this->proposalService->rejectProposal($proposal);

            return $this->successResponse('Proposal rejected successfully', $proposal, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Check if freelancer already submitted a proposal for a project
     */
    public function check(int $projectId): JsonResponse
    {
        try {
            $user = auth()->user();
            $freelancerProfile = $user->freelancerProfile;

            if (!$freelancerProfile) {
                return $this->errorResponse('Freelancer profile not found', 404);
            }

            $proposal = $this->proposalService->checkExistingProposal(
                $projectId,
                $freelancerProfile->id
            );

            return $this->successResponse('Success', $proposal, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
