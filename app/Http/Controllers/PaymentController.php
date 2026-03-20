<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payment\ProcessPaymentRequest;
use App\Services\PaymentService;
use App\Traits\ApiResponse;
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
            $payment = $this->paymentService->processPayment(
                $request->validated(),
                $projectId,
                auth()->id()
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
}
