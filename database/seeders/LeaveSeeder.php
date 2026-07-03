<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Leave;
use Carbon\Carbon;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leaves = [
            ['user_id' => 1, 'start_date' => Carbon::now()->addDays(15)->toDateString(), 'end_date' => Carbon::now()->addDays(17)->toDateString(), 'reason' => 'Vacation', 'status' => 'approved'],
        ];

        foreach ($leaves as $leave) {
            Leave::create($leave);
        }
    }
}
