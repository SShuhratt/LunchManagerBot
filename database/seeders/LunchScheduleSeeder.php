<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\LunchSchedule;

class LunchScheduleSeeder extends Seeder
{
    public function run(): void
    {
        LunchSchedule::create([
            'announce_time' => '12:45',
            'approval_time' => '12:55',
            'reminder_time' => '13:00',
            'reset_time' => '08:00',
            'enabled' => true,
        ]);
    }
}

