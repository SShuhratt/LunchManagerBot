<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use SergiX44\Nutgram\Nutgram;
use App\Models\LunchRequest;
use App\Jobs\SendLunchReminder;

/*
|--------------------------------------------------------------------------
| Nutgram Handlers
|--------------------------------------------------------------------------
|
| Here is where you can register telegram handlers for Nutgram. These
| handlers are loaded by the NutgramServiceProvider. Enjoy!
|
*/

$bot->onCommand('start', function (Nutgram $bot) {
    $bot->sendMessage("Welcome! I'll manage lunch breaks efficiently.");
})->description('The start command!');

$bot->onCommand('announce_lunch', function (Nutgram $bot) {
    $chatId = env('TELEGRAM_GROUP_ID');

    $bot->sendMessage($chatId, "Click below if you want lunch!", [
        'reply_markup' => json_encode([
            'inline_keyboard' => [[['text' => "I'm in", 'callback_data' => "lunch_request"]]]
        ])
    ]);
});

$bot->onCallbackQuery('lunch_request', function (Nutgram $bot) {
    $userId = $bot->userId();
    $userName = $bot->user()->first_name;

    LunchRequest::updateOrCreate(
        ['user_id' => $userId],
        ['name' => $userName, 'status' => 'work']
    );

    $bot->sendMessage($userId, "You've been added to the lunch request list.");
});

$bot->onCommand('process_lunch_list', function (Nutgram $bot) {
    // Get users who clicked "I'm in"
    $clickedUsers = LunchRequest::where('status', 'work')->pluck('user_id');

    // Get pre-approved list provided by the supervisor (assume stored in DB)
    $preApprovedUsers = SupervisorList::pluck('user_id');

    // Find common users in both lists
    $finalLunchList = LunchRequest::whereIn('user_id', $clickedUsers)
        ->whereIn('user_id', $preApprovedUsers)
        ->pluck('user_id');

    // Send lunch notifications to eligible users
    foreach ($finalLunchList as $userId) {
        $lunchStartTime = now()->addMinutes(3);
        LunchRequest::where('user_id', $userId)->update(['status' => 'at_lunch', 'lunch_time' => $lunchStartTime]);

        $bot->sendMessage($userId, "3 mins left until lunch starts!");
        $bot->sendMessage($userId, "It's time to have lunch!");
    }
});


$bot->onCommand('schedule_lunch_reminders', function (Nutgram $bot) {
    $usersAtLunch = LunchRequest::where('status', 'at_lunch')->pluck('user_id');

    foreach ($usersAtLunch as $userId) {
        SendLunchReminder::dispatch($userId, "5 mins left for lunch!")->delay(now()->addMinutes(25));
        SendLunchReminder::dispatch($userId, "Lunch ended, back to work!")->delay(now()->addMinutes(30));
    }
});

$bot->onCommand('approve_lunch', function (Nutgram $bot) {
    $approvedUsers = LunchRequest::where('status', 'work')
        ->where('is_supervisor', false)
        ->pluck('user_id');

    foreach ($approvedUsers as $userId) {
        $lunchStartTime = now()->addMinutes(3);
        LunchRequest::where('user_id', $userId)->update(['status' => 'at_lunch', 'lunch_time' => $lunchStartTime]);

        $bot->sendMessage($userId, "3 mins left until lunch starts!");
        $bot->sendMessage($userId, "It's time to have lunch!");
    }
});

