<?php

use App\Http\Controllers\LunchController;
use Illuminate\Support\Facades\Route;
use SergiX44\Nutgram\Nutgram;
use Illuminate\Support\Facades\Log;

Route::post('/', function (Nutgram $bot){
    $bot->onCommand('start', [LunchController::class, 'handleStart']);
    $bot->onCommand('announce_lunch', [LunchController::class, 'announceLunch']);
    $bot->onCallbackQuery('lunch_request', [LunchController::class, 'handleLunchRequest']);
    $bot->onCommand('process_lunch_list', [LunchController::class, 'processLunchList']);
    $bot->onCommand('schedule_lunch_reminders', [LunchController::class, 'scheduleReminders']);
    $bot->onCommand('approve_lunch', [LunchController::class, 'approveLunch']);
    $bot->onCommand('lunch_status', [LunchController::class, 'lunchStatus']);
    $bot->onCommand('reset_lunch', [LunchController::class, 'resetLunch']);
    $bot->run();
});

