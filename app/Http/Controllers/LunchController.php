<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\LunchRequest;
use App\Models\SupervisorList;
use App\Jobs\SendLunchReminder;
use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Facades\Cache;

class LunchController extends Controller
{
    public function announceLunch(Nutgram $bot)
    {
        $chatId = env('TELEGRAM_GROUP_ID');

        $bot->sendMessage($chatId, "Click below if you want lunch!", [
            'reply_markup' => [
                'inline_keyboard' => [[
                    ['text' => "I'm in", 'callback_data' => "lunch_request"]
                ]]
            ]
        ]);
    }

    public function handleLunchRequest(Nutgram $bot)
    {
        $userId = $bot->userId();
        $userName = $bot->user()->first_name;

        LunchRequest::updateOrCreate(
            ['user_id' => $userId],
            ['name' => $userName, 'status' => 'requested']
        );

        $bot->answerCallbackQuery("You've been added to the lunch request list!");
        $bot->sendMessage($userId, "You've been added to the lunch request list.");
    }

    public function processLunchList(Nutgram $bot)
    {
        try {
            $requestedUsers = LunchRequest::where('status', 'requested')->pluck('user_id');
            $preApprovedUsers = SupervisorList::active()->pluck('user_id');

            $finalLunchList = LunchRequest::whereIn('user_id', $requestedUsers)
                ->whereIn('user_id', $preApprovedUsers)
                ->get();

            if ($finalLunchList->isEmpty()) {
                $bot->sendMessage("No users are eligible for lunch at this time.");
                return;
            }

            foreach ($finalLunchList as $lunchRequest) {
                $userId = $lunchRequest->user_id;
                $lunchStartTime = now()->addMinutes(3);

                $lunchRequest->update([
                    'status' => 'at_lunch',
                    'lunch_time' => $lunchStartTime
                ]);

                $bot->sendMessage($userId, "3 mins left until lunch starts!");

                dispatch(function() use ($bot, $userId) {
                    $bot->sendMessage($userId, "It's time to have lunch!");
                })->delay(now()->addMinutes(3));
            }

            $bot->sendMessage("Lunch process initiated for " . $finalLunchList->count() . " users.");
        } catch (\Exception $e) {
            $bot->sendMessage("Error processing lunch list: " . $e->getMessage());
        }
    }

    public function scheduleReminders(Nutgram $bot)
    {
        try {
            $usersAtLunch = LunchRequest::where('status', 'at_lunch')->get();

            if ($usersAtLunch->isEmpty()) {
                $bot->sendMessage("No users are currently at lunch.");
                return;
            }

            foreach ($usersAtLunch as $lunchRequest) {
                $userId = $lunchRequest->user_id;

                SendLunchReminder::dispatch($userId, "5 mins left for lunch!")
                    ->delay(now()->addMinutes(25));

                SendLunchReminder::dispatch($userId, "Lunch ended, back to work!")
                    ->delay(now()->addMinutes(30));
            }

            $bot->sendMessage("Lunch reminders scheduled for " . $usersAtLunch->count() . " users.");
        } catch (\Exception $e) {
            $bot->sendMessage("Error scheduling reminders: " . $e->getMessage());
        }
    }

    public function approveLunch(Nutgram $bot)
    {
        try {
            $approvedUsers = LunchRequest::where('status', 'requested')
                ->where('is_supervisor', false)
                ->get();

            if ($approvedUsers->isEmpty()) {
                $bot->sendMessage("No users to approve for lunch.");
                return;
            }

            foreach ($approvedUsers as $lunchRequest) {
                $userId = $lunchRequest->user_id;
                $lunchStartTime = now()->addMinutes(3);

                $lunchRequest->update([
                    'status' => 'at_lunch',
                    'lunch_time' => $lunchStartTime
                ]);

                $bot->sendMessage($userId, "Your lunch has been approved! 3 mins left until lunch starts!");

                dispatch(function() use ($bot, $userId) {
                    $bot->sendMessage($userId, "It's time to have lunch!");
                })->delay(now()->addMinutes(3));
            }

            $bot->sendMessage("Lunch approved for " . $approvedUsers->count() . " users.");
        } catch (\Exception $e) {
            $bot->sendMessage("Error approving lunch: " . $e->getMessage());
        }
    }

    public function lunchStatus(Nutgram $bot)
    {
        try {
            $requested = LunchRequest::where('status', 'requested')->count();
            $atLunch = LunchRequest::where('status', 'at_lunch')->count();

            $message = "\ud83d\udcca Lunch Status:\n";
            $message .= "\u2022 Requested: {$requested}\n";
            $message .= "\u2022 At Lunch: {$atLunch}";

            $bot->sendMessage($message);
        } catch (\Exception $e) {
            $bot->sendMessage("Error getting lunch status: " . $e->getMessage());
        }
    }

    public function resetLunch(Nutgram $bot)
    {
        try {
            LunchRequest::query()->update(['status' => 'available']);
            $bot->sendMessage("All lunch statuses have been reset.");
        } catch (\Exception $e) {
            $bot->sendMessage("Error resetting lunch status: " . $e->getMessage());
        }
    }
}
