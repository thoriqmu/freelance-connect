<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService) {}

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->register($request->validated());

            return $this->successResponse(
                'User registered successfully',
                ['id' => $user->id, 'name' => $user->name, 'email' => $user->email, 'role' => $user->role],
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode() ?: 500);
        }
    }

    /**
     * Login user
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->email, $request->password);

            if (!$result) {
                return $this->errorResponse('Invalid email or password', 401);
            }

            return $this->successResponse(
                'Login successful',
                [
                    'user' => [
                        'id' => $result['user']->id,
                        'name' => $result['user']->name,
                        'email' => $result['user']->email,
                        'role' => $result['user']->role,
                        'avatar' => $result['user']->avatar,
                    ],
                    'token' => $result['token'],
                ],
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Get current user info
     */
    public function me(): JsonResponse
    {
        try {
            $user = auth()->user();

            return $this->successResponse(
                'Success',
                [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'avatar' => $user->avatar,
                    'created_at' => $user->created_at,
                ],
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Logout user
     */
    public function logout(): JsonResponse
    {
        try {
            $this->authService->logout(auth()->user());

            return $this->successResponse('Logged out successfully', null, 200);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
