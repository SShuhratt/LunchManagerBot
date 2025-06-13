<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LunchRequest;
use SergiX44\Nutgram\Nutgram;
use Laravel\Prompts\info;
use Illuminate\Console\Scheduling\Schedule;

class ApproveLunch extends Command
{
    protected $signature = 'lunch:approve';
    protected $description = 'Approve lunch requests for non-supervisors';

    public function handle(): void
    {
        $bot = app(Nutgram::class);

        $approvedUsers = LunchRequest::where('status', 'requested')
            ->where('is_supervisor', false)
            ->get();

        if ($approvedUsers->isEmpty()) {
            info("No users to approve for lunch.");
            return;
        }

        foreach ($approvedUsers as $lunchRequest) {
            $userId = $lunchRequest->user_id;
            $lunchStartTime = now()->addMinutes(5);

            $lunchRequest->update([
                'status' => 'at_lunch',
                'lunch_time' => $lunchStartTime,
            ]);

            $bot->sendMessage($userId, "Your lunch has been approved! 5 mins left until lunch starts!");

            dispatch(function () use ($bot, $userId) {
                $bot->sendMessage($userId, "It's time to have lunch!");
            })->delay(now()->addMinutes(5));
        }

        info("Lunch approved for {$approvedUsers->count()} users.");
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->dailyAt('12:55');
    }
}
