<?php

namespace App\Http\Controllers;

use App\Models\LunchRequest;
use App\Models\LunchSchedule;
use App\Models\SupervisorList;
use App\Jobs\SendLunchReminder;
use Illuminate\Support\Facades\Cache;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class LunchController extends Controller
{
    public function handleStart(Nutgram $bot)
    {
        $userId = $bot->chatId();
        $supervisor = SupervisorList::where('user_id', $userId)->first();

        if ($supervisor && $supervisor->is_active) {
            $bot->sendMessage(
                text: 'Welcome, Supervisor!',
                reply_markup: InlineKeyboardMarkup::make()
                    ->addRow(
                        InlineKeyboardButton::make('Announce Lunch', callback_data: '/announce_lunch'),
                        InlineKeyboardButton::make('Process Lunch List', callback_data: '/process_lunch_list')
                    )
                    ->addRow(
                        InlineKeyboardButton::make('Schedule Reminders', callback_data: '/schedule_lunch_reminders'),
                        InlineKeyboardButton::make('Approve Lunch', callback_data: '/approve_lunch')
                    )
                    ->addRow(
                        InlineKeyboardButton::make('Lunch Status', callback_data: '/lunch_status'),
                        InlineKeyboardButton::make('Reset Lunch', callback_data: '/reset_lunch')
                    )
            );
        } elseif ($supervisor && !$supervisor->is_active) {
            $bot->sendMessage("Welcome, {$supervisor->name}. You are registered as a supervisor but currently *not active*.", ['parse_mode' => 'Markdown']);
        } else {
            $announcementTime = optional(LunchSchedule::first())->announce_time ?? 'not scheduled yet';
            $bot->sendMessage("Hi! 👋\nLunch is usually announced at *{$announcementTime}*.\nStay tuned and click the button when it's up!", ['parse_mode' => 'Markdown']);
        }
    }

    public function announceLunch(Nutgram $bot)
    {
        $auth = $this->isAuthorizedSupervisor($bot);

        if ($auth === 'not_registered') {
            $bot->sendMessage("You are *not authorized* to perform this command.", ['parse_mode' => 'Markdown']);
            return;
        }

        if ($auth === 'inactive') {
            $bot->sendMessage("You are a supervisor but currently *not active*.", ['parse_mode' => 'Markdown']);
            return;
        }

        $chatId = config('nutgram.telegram.group_id');
        $bot->sendMessage(
            text: '🍽️ Click below if you want lunch!',
            reply_markup: InlineKeyboardMarkup::make()
                ->addRow(
                    InlineKeyboardButton::make("I'm in", callback_data: 'lunch_request'),
                )
        );
    }

    public function handleLunchRequest(Nutgram $bot)
    {
        $userId = $bot->userId();
        $userName = $bot->user()->first_name ?? 'Unknown';

        LunchRequest::updateOrCreate(
            ['user_id' => $userId],
            ['name' => $userName, 'status' => 'requested']
        );

        $bot->answerCallbackQuery("You've been added to the lunch request list!");
        $bot->sendMessage("✅ You’ve been added to the lunch request list.", chat_id: $userId);
    }

    public function processLunchList(Nutgram $bot)
    {
        if ($this->denyIfNotSupervisor($bot)) return;

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

                $bot->sendMessage("⏳ 5 minutes left until lunch starts!", chat_id: $userId);

                dispatch(function () use ($bot, $userId) {
                    $bot->sendMessage("🍴 It's time to have lunch!", chat_id: $userId);
                })->delay(now()->addMinutes(5));
            }

            $bot->sendMessage("✅ Lunch process initiated for " . $finalLunchList->count() . " users.");
        } catch (\Exception $e) {
            $bot->sendMessage("❌ Error processing lunch list: " . $e->getMessage());
        }
    }

    public function scheduleReminders(Nutgram $bot)
    {
        if ($this->denyIfNotSupervisor($bot)) return;

        try {
            $usersAtLunch = LunchRequest::where('status', 'at_lunch')->get();

            if ($usersAtLunch->isEmpty()) {
                $bot->sendMessage("ℹ️ No users are currently at lunch.");
                return;
            }

            foreach ($usersAtLunch as $lunchRequest) {
                $userId = $lunchRequest->user_id;

                SendLunchReminder::dispatch($userId, "⏳ 5 minutes left for lunch!")
                    ->delay(now()->addMinutes(25));

                SendLunchReminder::dispatch($userId, "🔔 Lunch ended, back to work!")
                    ->delay(now()->addMinutes(30));
            }

            $bot->sendMessage("✅ Reminders scheduled for " . $usersAtLunch->count() . " users.");
        } catch (\Exception $e) {
            $bot->sendMessage("❌ Error scheduling reminders: " . $e->getMessage());
        }
    }

    public function approveLunch(Nutgram $bot)
    {
        if ($this->denyIfNotSupervisor($bot)) return;

        try {
            $approvedUsers = LunchRequest::where('status', 'requested')
                ->where('is_supervisor', false)
                ->get();

            if ($approvedUsers->isEmpty()) {
                $bot->sendMessage("ℹ️ No users to approve for lunch.");
                return;
            }

            foreach ($approvedUsers as $lunchRequest) {
                $userId = $lunchRequest->user_id;
                $lunchStartTime = now()->addMinutes(5);

                $lunchRequest->update([
                    'status' => 'at_lunch',
                    'lunch_time' => $lunchStartTime
                ]);

                $bot->sendMessage("✅ Your lunch has been approved!\n⏳ 5 minutes left until lunch starts!", chat_id: $userId);

                dispatch(function () use ($bot, $userId) {
                    $bot->sendMessage("🍴 It's time to have lunch!", chat_id: $userId);
                })->delay(now()->addMinutes(5));
            }

            Cache::forget('lunch_requests_all');
            Cache::forget('lunch_status_count');

            $bot->sendMessage("✅ Lunch approved for " . $approvedUsers->count() . " users.");
        } catch (\Exception $e) {
            $bot->sendMessage("❌ Error approving lunch: " . $e->getMessage());
        }
    }


    public function lunchStatus(Nutgram $bot)
    {
        if ($this->denyIfNotSupervisor($bot)) return;

        try {
            $statusCount = $this->getCachedLunchStatusCount();

            $message = "📊 *Lunch Status:*\n";
            $message .= "• Requested: {$statusCount['requested']}\n";
            $message .= "• At Lunch: {$statusCount['at_lunch']}";

            $bot->sendMessage($message, ['parse_mode' => 'Markdown']);
        } catch (\Exception $e) {
            $bot->sendMessage("❌ Error getting lunch status: " . $e->getMessage());
        }
    }


    public function resetLunch(Nutgram $bot)
    {
        if ($this->denyIfNotSupervisor($bot)) return;

        try {
            LunchRequest::query()->update(['status' => 'available']);

            Cache::forget('lunch_requests_all');
            Cache::forget('lunch_status_count');

            $bot->sendMessage("🔄 All lunch statuses have been reset.");
        } catch (\Exception $e) {
            $bot->sendMessage("❌ Error resetting lunch status: " . $e->getMessage());
        }
    }


    protected function isAuthorizedSupervisor(Nutgram $bot): bool|string
    {
        $userId = $bot->chatId();
        $supervisor = SupervisorList::where('user_id', $userId)->first();

        if (!$supervisor) return 'not_registered';
        if (!$supervisor->is_active) return 'inactive';

        return true;
    }

    protected function denyIfNotSupervisor(Nutgram $bot): bool
    {
        $auth = $this->isAuthorizedSupervisor($bot);

        if ($auth === 'not_registered') {
            $bot->sendMessage("⛔ You are *not authorized* to perform this command.", ['parse_mode' => 'Markdown']);
            return true;
        }

        if ($auth === 'inactive') {
            $bot->sendMessage("⚠️ You are a supervisor but currently *not active*.", ['parse_mode' => 'Markdown']);
            return true;
        }

        return false;
    }

    public function getCachedLunchRequests()
    {
        return Cache::remember('lunch_requests_all', 600, function () {
            return LunchRequest::all();
        });
    }

    public function getCachedLunchStatusCount(): array
    {
        return Cache::remember('lunch_status_count', 600, function () {
            return [
                'requested' => LunchRequest::where('status', 'requested')->count(),
                'at_lunch' => LunchRequest::where('status', 'at_lunch')->count(),
            ];
        });
    }

}
