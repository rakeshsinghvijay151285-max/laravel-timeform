<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TimeLog;
use Carbon\Carbon;

class TimeLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timeLogs = [
            ['user_id' => 1, 'project_id' => 1, 'work_date' => Carbon::now()->subDays(5)->toDateString(), 'task_description' => 'UI mockup design', 'hours' => 3, 'minutes' => 30],
            ['user_id' => 1, 'project_id' => 1, 'work_date' => Carbon::now()->subDays(5)->toDateString(), 'task_description' => 'Prototype development', 'hours' => 2, 'minutes' => 15],
            ['user_id' => 1, 'project_id' => 2, 'work_date' => Carbon::now()->subDays(4)->toDateString(), 'task_description' => 'API endpoint setup', 'hours' => 4, 'minutes' => 20],
            ['user_id' => 1, 'project_id' => 3, 'work_date' => Carbon::now()->subDays(3)->toDateString(), 'task_description' => 'Database schema review', 'hours' => 2, 'minutes' => 45],
            ['user_id' => 1, 'project_id' => 4, 'work_date' => Carbon::now()->subDays(2)->toDateString(), 'task_description' => 'Third party integration', 'hours' => 3, 'minutes' => 0],
        ];

        foreach ($timeLogs as $timeLog) {
            TimeLog::create($timeLog);
        }
    }
}
