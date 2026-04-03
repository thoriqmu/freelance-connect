<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use App\Models\Project;
use App\Models\Proposal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    use ApiResponse;

    public function __construct(private PaymentService $paymentService) {}

    /**
     * Get payment for a project
     */
    public function show(int $projectId): JsonResponse
    {
        try {
            $payment = $this->paymentService->getProjectPayment($projectId);

            if (!$payment) {
                return $this->errorResponse('No payment found for this project', 404);
            }

            return $this->successResponse('Success', $payment, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get my payments (client only)
     */
    public function myPayments(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $payments = $this->paymentService->getUserPayments(
                $user->id,
                $request->input('per_page', 10)
            );

            return $this->successResponse('Success', $payments, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Process payment for a completed project
     */
    public function process(int $projectId, ProcessPaymentRequest $request): JsonResponse
    {
        try {
            $project = Project::findOrFail($projectId);
            // Assuming proposal_id is sent in request
            $proposal = Proposal::findOrFail($request->input('proposal_id'));

            $payment = $this->paymentService->initiateEscrowPayment(
                $project,
                $proposal
            );

            return $this->successResponse('Payment processed successfully', $payment, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Get payment detail
     */
    public function detail(int $id): JsonResponse
    {
        try {
            $payment = $this->paymentService->getPaymentDetail($id);

            if (!$payment) {
                return $this->notFoundResponse('Payment not found');
            }

            return $this->successResponse('Success', $payment, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Retry payout for a project (Freelancer only)
     */
    public function retryPayout(Project $project): JsonResponse
    {
        try {
            // Check if user is the assigned freelancer
            // In the payment table freelancer_id is related to users.id
            if ($project->freelancer_id !== auth()->user()->freelancer_profile?->id) {
                 // Check both ways just in case
                 if ($project->freelancer->user->id !== auth()->id()) {
                    return $this->forbiddenResponse('You are not the assigned freelancer for this project.');
                 }
            }

            // Check if project is completed
            if ($project->status !== \App\Enums\ProjectStatus::COMPLETED->value) {
                return $this->errorResponse('Payout can only be retried for completed projects.', 400);
            }

            $success = $this->paymentService->retryPayout($project);

            if ($success) {
                return $this->successResponse('Payout initiated successfully');
            } else {
                return $this->errorResponse('Failed to initiate payout. Please ensure you have a primary bank account set up.', 422);
            }
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
