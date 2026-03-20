<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Project;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentService
{
    /**
     * Get payment for a project
     */
    public function getProjectPayment(int $projectId): ?Payment
    {
        return Payment::where('project_id', $projectId)->first();
    }

    /**
     * Get user payments (as client who paid)
     */
    public function getUserPayments(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Payment::whereHas('project.client.user', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        })
            ->with(['project.freelancer.user'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Process payment (dummy implementation)
     */
    public function processPayment(array $data, int $projectId, int $userId): Payment
    {
        try {
            $project = Project::findOrFail($projectId);

            // Verify user is the project client
            if ($project->client_id !== auth()->user()->clientProfile?->id) {
                throw new \Exception('Only project client can process payment.', 403);
            }

            // Check if project is completed
            if ($project->status !== 'completed') {
                throw new \Exception('Payment can only be processed for completed projects.', 403);
            }

            // Check if payment already exists
            $existingPayment = Payment::where('project_id', $projectId)->first();
            if ($existingPayment) {
                throw new \Exception('Payment already exists for this project.', 422);
            }

            // Dummy payment processing - in real app, integrate with payment gateway
            $payment = Payment::create([
                'project_id' => $projectId,
                'transaction_id' => 'TXN-' . Str::random(16),
                'amount' => $project->budget,
                'status' => 'completed', // In MVP, auto-complete dummy payment
                'payment_method' => $data['payment_method'],
            ]);

            Log::info('Payment processed', ['payment_id' => $payment->id, 'project_id' => $projectId]);

            return $payment;
        } catch (\Exception $e) {
            Log::error('Process payment failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get payment details
     */
    public function getPaymentDetail(int $paymentId): ?Payment
    {
        return Payment::with(['project.client.user', 'project.freelancer.user'])->find($paymentId);
    }
}
