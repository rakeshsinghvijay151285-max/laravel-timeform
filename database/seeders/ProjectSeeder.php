<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $projects = [
            ['name' => 'Website Redesign', 'description' => 'Complete redesign of company website', 'status' => 'active'],
            ['name' => 'Mobile App Development', 'description' => 'iOS and Android application development', 'status' => 'active'],
            ['name' => 'Database Migration', 'description' => 'Migration from legacy database to new system', 'status' => 'active'],
            ['name' => 'API Integration', 'description' => 'Integration of third-party APIs', 'status' => 'active'],
            ['name' => 'Security Audit', 'description' => 'System security audit and vulnerability assessment', 'status' => 'active'],
            ['name' => 'Performance Optimization', 'description' => 'Optimize application performance', 'status' => 'active'],
            ['name' => 'Testing & QA', 'description' => 'Quality assurance and testing', 'status' => 'active'],
            ['name' => 'Documentation', 'description' => 'Technical documentation and user guides', 'status' => 'active'],
        ];

        foreach ($projects as $project) {
            Project::create($project);
        }
    }
}
