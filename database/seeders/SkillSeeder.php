<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Skill;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            'Web Programmer', 'Frontend Developer', 'Backend Developer', 'Fullstack Developer',
            'Mobile App Developer', 'Android Developer', 'iOS Developer', 'Flutter Developer',
            'React Native Developer', 'UI/UX Designer', 'Graphic Designer', 'Logo Designer',
            'Data Scientist', 'Data Analyst', 'Data Engineer', 'Machine Learning Engineer',
            'DevOps Engineer', 'Cloud Architect', 'System Administrator', 'Cybersecurity Expert',
            'SEO Specialist', 'Digital Marketer', 'Content Writer', 'Copywriter',
            'Video Editor', 'Animator', '3D Artist', 'Illustrator',
            'Project Manager', 'Scrum Master', 'Product Manager', 'Business Analyst',
            'Software Tester', 'QA Engineer', 'Game Developer', 'Unity Developer',
            'Unreal Engine Developer', 'Blockchain Developer', 'Smart Contract Developer',
            'IT Support', 'Network Engineer', 'Database Administrator', 'WordPress Developer',
            'Shopify Developer', 'E-commerce Specialist', 'Virtual Assistant', 'Customer Support',
            'Translator', 'Voice Over Artist', 'Audio Engineer', 'Sales Representative',
            'Lead Generator', 'Social Media Manager', 'Email Marketer', 'Public Relations Specialist'
        ];

        foreach ($skills as $skill) {
            Skill::firstOrCreate(['title' => $skill]);
        }
    }
}
