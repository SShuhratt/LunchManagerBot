<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LunchRequest;
use App\Jobs\SendLunchReminder;
use Laravel\Prompts\info;
use Illuminate\Console\Scheduling\Schedule;

class ScheduleLunchReminder extends Command
{
    protected $signature = 'lunch:schedule-reminders';
    protected $description = 'Schedule lunch ending reminders';

    public function handle(): void
    {
        $usersAtLunch = LunchRequest::where('status', 'at_lunch')->get();

        if ($usersAtLunch->isEmpty()) {
            info("No users are currently at lunch.");
            return;
        }

        foreach ($usersAtLunch as $lunchRequest) {
            $userId = $lunchRequest->user_id;

            SendLunchReminder::dispatch($userId, "5 mins left for lunch!")
                ->delay(now()->addMinutes(25));

            SendLunchReminder::dispatch($userId, "Lunch ended, back to work!")
                ->delay(now()->addMinutes(30));
        }

        info("Reminders scheduled for {$usersAtLunch->count()} users.");
    }
}
