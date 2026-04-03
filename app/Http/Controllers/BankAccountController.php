<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BankAccountController extends Controller
{
    use ApiResponse;

    /**
     * Get user's bank accounts
     */
    public function index(): JsonResponse
    {
        $accounts = auth()->user()->bankAccounts()->orderBy('is_primary', 'desc')->get();
        return $this->successResponse('Success', $accounts);
    }

    /**
     * Store a new bank account
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bank_code' => 'required|string',
            'account_number' => 'required|string',
            'account_name' => 'required|string',
            'is_primary' => 'boolean',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $user = auth()->user();

                // If this is the first account, or marked as primary
                if ($user->bankAccounts()->count() === 0) {
                    $validated['is_primary'] = true;
                }

                if ($validated['is_primary'] ?? false) {
                    $user->bankAccounts()->update(['is_primary' => false]);
                }

                $account = $user->bankAccounts()->create($validated);

                return $this->successResponse('Bank account added successfully', $account, 201);
            });
        } catch (\Exception $e) {
            Log::error('Failed to store bank account', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to add bank account', 500);
        }
    }

    /**
     * Update bank account
     */
    public function update(Request $request, BankAccount $bankAccount): JsonResponse
    {
        if ($bankAccount->user_id !== auth()->id()) {
            return $this->forbiddenResponse();
        }

        $validated = $request->validate([
            'account_name' => 'string',
            'is_primary' => 'boolean',
        ]);

        try {
            return DB::transaction(function () use ($validated, $bankAccount) {
                if ($validated['is_primary'] ?? false) {
                    auth()->user()->bankAccounts()->update(['is_primary' => false]);
                }

                $bankAccount->update($validated);

                return $this->successResponse('Bank account updated successfully', $bankAccount);
            });
        } catch (\Exception $e) {
            Log::error('Failed to update bank account', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to update bank account', 500);
        }
    }

    /**
     * Delete bank account
     */
    public function destroy(BankAccount $bankAccount): JsonResponse
    {
        if ($bankAccount->user_id !== auth()->id()) {
            return $this->forbiddenResponse();
        }

        if ($bankAccount->is_primary && auth()->user()->bankAccounts()->count() > 1) {
            return $this->errorResponse('Cannot delete primary account while other accounts exist. Set another account as primary first.', 422);
        }

        $bankAccount->delete();

        return $this->successResponse('Bank account deleted successfully');
    }

    /**
     * Set account as primary
     */
    public function setPrimary(BankAccount $bankAccount): JsonResponse
    {
        if ($bankAccount->user_id !== auth()->id()) {
            return $this->forbiddenResponse();
        }

        try {
            DB::transaction(function () use ($bankAccount) {
                auth()->user()->bankAccounts()->update(['is_primary' => false]);
                $bankAccount->update(['is_primary' => true]);
            });

            return $this->successResponse('Primary bank account updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to set primary bank account', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to update primary account', 500);
        }
    }
}
