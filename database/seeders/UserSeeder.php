<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clients
        $clients = [
            [
                'name' => 'Jane Doe',
                'email' => 'jane.doe@example.com',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
            ],
        ];

        foreach ($clients as $client) {
            $userId = DB::table('users')->insertGetId([
                'name' => $client['name'],
                'email' => $client['email'],
                'password' => Hash::make('password123'),
                'role' => 'client',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('client_profiles')->insert([
                'user_id' => $userId,
                'company_name' => $client['name'] . ' Company',
                'company_field' => 'Technology',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Freelancers
        $freelancers = [
            [
                'name' => 'Eva Turner',
                'email' => 'eva.turner@example.com',
            ],
            [
                'name' => 'Mark Bennet',
                'email' => 'mark.bennet@example.com',
            ],
            [
                'name' => 'Tom Holland',
                'email' => 'tom.holland@example.com',
            ],
        ];

        foreach ($freelancers as $freelancer) {
            $userId = DB::table('users')->insertGetId([
                'name' => $freelancer['name'],
                'email' => $freelancer['email'],
                'password' => Hash::make('password123'),
                'role' => 'freelancer',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('freelancer_profiles')->insert([
                'user_id' => $userId,
                'skills' => json_encode(['Laravel', 'PHP']),
                'hourly_rate' => 15.00,
                'portfolio_url' => null,
                'bio' => 'Experienced freelancer',
                'availability' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
