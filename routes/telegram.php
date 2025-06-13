<?php
/** @var SergiX44\Nutgram\Nutgram $bot */

use App\Http\Controllers\LunchController;
use SergiX44\Nutgram\Nutgram;
use App\Models\LunchRequest;
use App\Models\SupervisorList;
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

$bot->onCommand('announce_lunch', [LunchController::class, 'announceLunch']);
$bot->onCallbackQuery('lunch_request', [LunchController::class, 'handleLunchRequest']);
$bot->onCommand('process_lunch_list', [LunchController::class, 'processLunchList']);
$bot->onCommand('schedule_lunch_reminders', [LunchController::class, 'scheduleReminders']);
$bot->onCommand('approve_lunch', [LunchController::class, 'approveLunch']);
$bot->onCommand('lunch_status', [LunchController::class, 'lunchStatus']);
$bot->onCommand('reset_lunch', [LunchController::class, 'resetLunch']);

//$bot->onCommand('announce_lunch', function (Nutgram $bot) {
//    $chatId = env('TELEGRAM_GROUP_ID');
//
//    $bot->sendMessage($chatId, "Click below if you want lunch!", [
//        'reply_markup' => [
//            'inline_keyboard' => [[
//                ['text' => "I'm in", 'callback_data' => "lunch_request"]
//            ]]
//        ]
//    ]);
//});
//
//$bot->onCallbackQuery('lunch_request', function (Nutgram $bot) {
//    $userId = $bot->userId();
//    $userName = $bot->user()->first_name;
//
//    LunchRequest::updateOrCreate(
//        ['user_id' => $userId],
//        ['name' => $userName, 'status' => 'requested'] // Changed from 'work' to 'requested'
//    );
//
//    // Answer the callback query to remove loading state
//    $bot->answerCallbackQuery("You've been added to the lunch request list!");
//
//    // Also send a message to the user
//    $bot->sendMessage($userId, "You've been added to the lunch request list.");
//});
//
//$bot->onCommand('process_lunch_list', function (Nutgram $bot) {
//    try {
//        $requestedUsers = LunchRequest::where('status', 'requested')->pluck('user_id');
//
//        // Get pre-approved list provided by the supervisor
//        $preApprovedUsers = SupervisorList::active()->pluck('user_id');
//
//        $finalLunchList = LunchRequest::whereIn('user_id', $requestedUsers)
//            ->whereIn('user_id', $preApprovedUsers)
//            ->get();
//
//        if ($finalLunchList->isEmpty()) {
//            $bot->sendMessage("No users are eligible for lunch at this time.");
//            return;
//        }
//
//        // Send lunch notifications to eligible users
//        foreach ($finalLunchList as $lunchRequest) {
//            $userId = $lunchRequest->user_id;
//            $lunchStartTime = now()->addMinutes(3);
//
//            $lunchRequest->update([
//                'status' => 'at_lunch',
//                'lunch_time' => $lunchStartTime
//            ]);
//
//            $bot->sendMessage($userId, "3 mins left until lunch starts!");
//
//            // Schedule the lunch start message
//            dispatch(function() use ($bot, $userId) {
//                $bot->sendMessage($userId, "It's time to have lunch!");
//            })->delay(now()->addMinutes(3));
//        }
//
//        $bot->sendMessage("Lunch process initiated for " . $finalLunchList->count() . " users.");
//
//    } catch (\Exception $e) {
//        $bot->sendMessage("Error processing lunch list: " . $e->getMessage());
//    }
//});
//
//$bot->onCommand('schedule_lunch_reminders', function (Nutgram $bot) {
//    try {
//        $usersAtLunch = LunchRequest::where('status', 'at_lunch')->get();
//
//        if ($usersAtLunch->isEmpty()) {
//            $bot->sendMessage("No users are currently at lunch.");
//            return;
//        }
//
//        foreach ($usersAtLunch as $lunchRequest) {
//            $userId = $lunchRequest->user_id;
//
//            // Schedule 5 minutes warning (25 minutes after lunch start)
//            SendLunchReminder::dispatch($userId, "5 mins left for lunch!")
//                ->delay(now()->addMinutes(25));
//
//            // Schedule end of lunch (30 minutes after lunch start)
//            SendLunchReminder::dispatch($userId, "Lunch ended, back to work!")
//                ->delay(now()->addMinutes(30));
//        }
//
//        $bot->sendMessage("Lunch reminders scheduled for " . $usersAtLunch->count() . " users.");
//
//    } catch (\Exception $e) {
//        $bot->sendMessage("Error scheduling reminders: " . $e->getMessage());
//    }
//});
//
//$bot->onCommand('approve_lunch', function (Nutgram $bot) {
//    try {
//        // Get users who requested lunch but are not supervisors
//        $approvedUsers = LunchRequest::where('status', 'requested')
//            ->where('is_supervisor', false)
//            ->get();
//
//        if ($approvedUsers->isEmpty()) {
//            $bot->sendMessage("No users to approve for lunch.");
//            return;
//        }
//
//        foreach ($approvedUsers as $lunchRequest) {
//            $userId = $lunchRequest->user_id;
//            $lunchStartTime = now()->addMinutes(3);
//
//            $lunchRequest->update([
//                'status' => 'at_lunch',
//                'lunch_time' => $lunchStartTime
//            ]);
//
//            $bot->sendMessage($userId, "Your lunch has been approved! 3 mins left until lunch starts!");
//
//            // Schedule the lunch start message
//            dispatch(function() use ($bot, $userId) {
//                $bot->sendMessage($userId, "It's time to have lunch!");
//            })->delay(now()->addMinutes(3));
//        }
//
//        $bot->sendMessage("Lunch approved for " . $approvedUsers->count() . " users.");
//
//    } catch (\Exception $e) {
//        $bot->sendMessage("Error approving lunch: " . $e->getMessage());
//    }
//});
//
//// Additional helpful commands
//$bot->onCommand('lunch_status', function (Nutgram $bot) {
//    try {
//        $requested = LunchRequest::where('status', 'requested')->count();
//        $atLunch = LunchRequest::where('status', 'at_lunch')->count();
//
//        $message = "📊 Lunch Status:\n";
//        $message .= "• Requested: {$requested}\n";
//        $message .= "• At Lunch: {$atLunch}";
//
//        $bot->sendMessage($message);
//    } catch (\Exception $e) {
//        $bot->sendMessage("Error getting lunch status: " . $e->getMessage());
//    }
//});
//
//$bot->onCommand('reset_lunch', function (Nutgram $bot) {
//    try {
//        LunchRequest::query()->update(['status' => 'available']);
//        $bot->sendMessage("All lunch statuses have been reset.");
//    } catch (\Exception $e) {
//        $bot->sendMessage("Error resetting lunch status: " . $e->getMessage());
//    }
//});
