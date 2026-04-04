<?php

namespace App\Http\Controllers;

use App\Models\Earning;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EarningController extends Controller
{
    use ApiResponse;

    public function __construct(private PaymentService $paymentService) {}

    /**
     * Get freelancer earnings and balance
     */
    public function index(): JsonResponse
    {
        try {
            $user = auth()->user();
            
            $earnings = Earning::where('freelancer_id', $user->id)
                ->with(['project'])
                ->latest()
                ->get();

            $availableBalance = Earning::where('freelancer_id', $user->id)
                ->where('status', 'available')
                ->sum('amount');

            $totalWithdrawn = Earning::where('freelancer_id', $user->id)
                ->where('status', 'withdrawn')
                ->sum('amount');

            return $this->successResponse('Success', [
                'earnings' => $earnings,
                'available_balance' => (float) $availableBalance,
                'total_withdrawn' => (float) $totalWithdrawn,
            ]);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Withdraw a specific earning
     */
    public function withdraw(int $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            try {
                $earning = Earning::with('project.payment')->findOrFail($id);
                $user = auth()->user();

                if ($earning->freelancer_id !== $user->id) {
                    return $this->forbiddenResponse();
                }

                if ($earning->status !== 'available') {
                    return $this->errorResponse('This earning is already withdrawn or not available.', 422);
                }

                // Check if freelancer has a primary bank account
                $bankAccount = $user->bankAccounts()->where('is_primary', true)->first();
                if (!$bankAccount) {
                    return $this->errorResponse('Please set a primary bank account in Bank Accounts page before withdrawing.', 422);
                }

                // Trigger Xendit Payout via PaymentService
                $success = $this->paymentService->releaseEscrowPayment($earning->project);

                if ($success) {
                    $earning->update([
                        'status' => 'withdrawn',
                        'withdrawn_at' => now(),
                    ]);
                    return $this->successResponse('Withdrawal processed successfully', $earning);
                }

                return $this->errorResponse('Withdrawal deferred. Please ensure your bank account is correctly configured.', 422);
            } catch (\Exception $e) {
                return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
            }
        });
    }
}
