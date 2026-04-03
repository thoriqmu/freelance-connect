<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Enums\ProjectStatus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class XenditWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $callbackToken = config('services.xendit.webhook_token');
        $headerToken = $request->header('x-callback-token');

        if ($callbackToken && $headerToken !== $callbackToken) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->all();
        $externalId = $data['external_id'] ?? null;
        $status = $data['status'] ?? null;

        if (!$externalId || $status !== 'PAID') {
            return response()->json(['message' => 'Ignored'], 200);
        }

        try {
            DB::beginTransaction();

            $payment = Payment::where('transaction_id', $externalId)->first();

            if (!$payment) {
                Log::warning('Xendit webhook: Payment not found', ['external_id' => $externalId]);
                return response()->json(['message' => 'Payment not found'], 404);
            }

            if ($payment->status === 'in_escrow') {
                return response()->json(['message' => 'Already processed'], 200);
            }

            // Update payment
            $payment->update([
                'status' => 'in_escrow',
                'paid_at' => now(),
            ]);

            // Update project status to IN_PROGRESS
            $payment->project->update([
                'status' => ProjectStatus::IN_PROGRESS->value,
            ]);

            DB::commit();

            Log::info('Xendit webhook: Payment processed successfully', ['payment_id' => $payment->id]);

            return response()->json(['message' => 'OK'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Xendit webhook: Error processing payment', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }
}
