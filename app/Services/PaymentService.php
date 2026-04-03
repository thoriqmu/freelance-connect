<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\PaymentDetail;
use App\Models\PaymentFee;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Xendit\Configuration;
use Xendit\Invoice\InvoiceApi;
use Xendit\Invoice\CreateInvoiceRequest;
use App\Models\Disbursement;
use Xendit\Payout\PayoutApi;
use Xendit\Payout\CreatePayoutRequest;

class PaymentService
{
    public function __construct()
    {
        Configuration::setXenditKey(config('services.xendit.secret_key'));
    }

    /**
     * Get a custom Guzzle client with SSL verification disabled for local development.
     */
    private function getHttpClient(): \GuzzleHttp\Client
    {
        $options = [];
        
        // Disable SSL verification in local environment to avoid cURL error 77
        if (config('app.env') === 'local') {
            $options['verify'] = false;
        }
        
        return new \GuzzleHttp\Client($options);
    }

    /**
     * Release Escrow Payment (Disbursement)
     */
    public function releaseEscrowPayment(Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            $payment = $project->payment;

            if (!$payment || $payment->status !== 'in_escrow') {
                throw new \Exception('Payment is not in escrow or not found.', 422);
            }

            $feeRecord = $payment->paymentFee;
            $amountToRelease = $feeRecord->freelancer_amount;

            // Get freelancer's primary bank account
            $bankAccount = $payment->freelancer->bankAccounts()->where('is_primary', true)->first();

            if (!$bankAccount) {
                Log::warning('Payout deferred: Freelancer has no primary bank account configured.', ['project_id' => $project->id]);
                return false; // Return false to indicate payout was deferred
            }

            // Create Disbursement Record
            $disbursement = Disbursement::create([
                'payment_id' => $payment->id,
                'bank_account_id' => $bankAccount->id,
                'amount' => $amountToRelease,
                'status' => 'pending',
            ]);

            $channelCode = $bankAccount->bank_code;
            // Ensure ID_ prefix for Xendit Payouts API
            if (!str_starts_with($channelCode, 'ID_')) {
                $channelCode = 'ID_' . $channelCode;
            }

            // Call Xendit Payout API (Modern version of Disbursements)
            $apiInstance = new PayoutApi($this->getHttpClient());
            $createPayoutRequest = new CreatePayoutRequest([
                'reference_id' => (string) $disbursement->id,
                'channel_code' => $channelCode,
                'channel_properties' => [
                    'account_holder_name' => $bankAccount->account_name,
                    'account_number' => $bankAccount->account_number,
                ],
                'description' => "Payout for Project: {$project->title}",
                'amount' => (float) $amountToRelease,
                'currency' => 'IDR',
            ]);

            try {
                // Payout API requires an idempotency key
                $idempotencyKey = 'PAY-' . $disbursement->id . '-' . time();
                $response = $apiInstance->createPayout($idempotencyKey, null, $createPayoutRequest);

                $disbursement->update([
                    'xendit_disbursement_id' => $response['id'],
                    'status' => $response['status'] === 'COMPLETED' ? 'completed' : 'pending',
                    'disbursed_at' => $response['status'] === 'COMPLETED' ? now() : null,
                    'xendit_payload' => json_decode(json_encode($response), true),
                ]);

                $payment->update([
                    'status' => 'released',
                    'released_at' => now(),
                ]);

                Log::info('Escrow payment released successfully', ['payment_id' => $payment->id]);
                return true;
            } catch (\Xendit\XenditSdkException $e) {
                Log::error('Xendit payout API error', [
                    'message' => $e->getMessage(),
                    'full_error' => $e->getFullError(),
                    'project_id' => $project->id
                ]);
                throw new \Exception("Xendit Error: " . ($e->getFullError()['message'] ?? $e->getMessage()), 422);
            } catch (\Exception $e) {
                Log::error('Xendit payout generic error', ['error' => $e->getMessage()]);
                throw $e;
            }
        });
    }

    /**
     * Retry payout for a project that is already completed
     */
    public function retryPayout(Project $project): bool
    {
        return $this->releaseEscrowPayment($project);
    }

    /**
     * Initiate Escrow Payment when proposal is accepted
     */
    public function initiateEscrowPayment(Project $project, Proposal $proposal): Payment
    {
        return DB::transaction(function () use ($project, $proposal) {
            $amount = $proposal->bid_price;
            $platformFee = $amount * 0.1; // Example 10% fee
            $freelancerAmount = $amount - $platformFee;

            $payment = Payment::create([
                'project_id' => $project->id,
                'client_id' => $project->client->user->id,
                'freelancer_id' => $proposal->freelancer->user->id,
                'transaction_id' => 'ESC-' . strtoupper(Str::random(12)),
                'amount' => $amount,
                'status' => 'pending',
            ]);

            PaymentFee::create([
                'payment_id' => $payment->id,
                'platform_fee' => $platformFee,
                'freelancer_amount' => $freelancerAmount,
            ]);

            // Create Xendit Invoice
            $apiInstance = new InvoiceApi($this->getHttpClient());
            $createInvoiceRequest = new CreateInvoiceRequest([
                'external_id' => (string) $payment->transaction_id,
                'description' => "Escrow Payment for Project: {$project->title}",
                'amount' => (float) $amount,
                'payer_email' => $project->client->user->email,
                'customer' => [
                    'given_names' => $project->client->user->name,
                    'email' => $project->client->user->email,
                ],
                'invoice_duration' => 86400, // 24 hours
                'currency' => 'IDR',
                'items' => [
                    [
                        'name' => $project->title,
                        'description' => "Proposal bid by {$proposal->freelancer->user->name}",
                        'quantity' => 1,
                        'price' => (float) $amount,
                    ]
                ],
                'success_redirect_url' => config('app.url') . "/projects/{$project->id}",
            ]);

            try {
                $invoice = $apiInstance->createInvoice($createInvoiceRequest);

                PaymentDetail::create([
                    'payment_id' => $payment->id,
                    'gateway' => 'xendit',
                    'xendit_invoice_id' => $invoice['id'],
                    'invoice_url' => $invoice['invoice_url'],
                    'xendit_payload' => json_decode(json_encode($invoice), true),
                ]);

                Log::info('Xendit invoice created', ['payment_id' => $payment->id, 'invoice_id' => $invoice['id']]);
            } catch (\Exception $e) {
                Log::error('Xendit invoice creation failed', ['error' => $e->getMessage()]);
                throw $e;
            }

            return $payment;
        });
    }

    /**
     * Get payment for a project (with auto-healing for missing records)
     */
    public function getProjectPayment(int $projectId): ?Payment
    {
        $payment = Payment::with(['paymentDetail', 'paymentFee'])->where('project_id', $projectId)->first();

        // Auto-healing: If project is in waiting_payment but has no payment record (likely due to previous SSL/API error)
        if (!$payment) {
            $project = Project::find($projectId);
            if ($project && $project->status === 'waiting_payment') {
                $proposal = Proposal::where('project_id', $projectId)->where('status', 'accepted')->first();
                if ($proposal) {
                    Log::info('Healing missing payment for project', ['project_id' => $projectId]);
                    $payment = $this->initiateEscrowPayment($project, $proposal);
                    return $payment->load(['paymentDetail', 'paymentFee']);
                }
            }
        }

        return $payment;
    }

    /**
     * Get user payments (as client who paid)
     */
    public function getUserPayments(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Payment::where('client_id', $userId)
            ->with(['project', 'freelancer.user', 'paymentDetail'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get payment details
     */
    public function getPaymentDetail(int $paymentId): ?Payment
    {
        return Payment::with(['project.client.user', 'project.freelancer.user', 'paymentDetail', 'paymentFee'])->find($paymentId);
    }
}
