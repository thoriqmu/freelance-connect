<?php

namespace App\Services;

use App\Models\User;
use App\Models\ClientProfile;
use App\Models\FreelancerProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthService
{
    /**
     * Register a new user
     */
    public function register(array $data): User
    {
        try {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['role'],
            ]);

            // Create profile based on role
            if ($data['role'] === 'client') {
                ClientProfile::create([
                    'user_id' => $user->id,
                    'company_name' => '',
                    'company_field' => '',
                ]);
            } else {
                FreelancerProfile::create([
                    'user_id' => $user->id,
                    'skills' => [],
                    'hourly_rate' => 0,
                ]);
            }

            Log::info('User registered', ['user_id' => $user->id, 'email' => $user->email]);

            return $user;
        } catch (\Exception $e) {
            Log::error('Registration failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Login user and return token
     */
    public function login(string $email, string $password): ?array
    {
        try {
            $user = User::where('email', $email)->first();

            if (!$user || !Hash::check($password, $user->password)) {
                Log::warning('Login failed', ['email' => $email]);
                return null;
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            Log::info('User logged in', ['user_id' => $user->id, 'email' => $user->email]);

            return [
                'user' => $user,
                'token' => $token,
            ];
        } catch (\Exception $e) {
            Log::error('Login error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Logout user - revoke token
     */
    public function logout(User $user): bool
    {
        try {
            $user->tokens()->delete();
            Log::info('User logged out', ['user_id' => $user->id]);
            return true;
        } catch (\Exception $e) {
            Log::error('Logout error', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
