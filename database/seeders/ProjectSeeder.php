<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\ClientProfile;
use Illuminate\Support\Str;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = ClientProfile::all();
        if ($clients->isEmpty()) {
            $this->command->warn('No client profiles found. Please seed users and client profiles first.');
            return;
        }

        foreach (range(1, 20) as $i) {
            $client = $clients->random();
            Project::create([
                'client_id' => $client->id,
                'freelancer_id' => null,
                'title' => 'Project ' . $i . ' - ' . Str::random(8),
                'description' => 'This is a sample project description for project ' . $i . '.',
                'budget' => rand(500, 5000),
                'timeline' => rand(1, 12),
                'status' => 'open',
            ]);
        }
    }
}
