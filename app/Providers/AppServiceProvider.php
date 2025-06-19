<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use SergiX44\Nutgram\Nutgram;

use App\Console\Commands\AnnounceLunch;
use App\Console\Commands\ApproveLunch;
use App\Console\Commands\LunchReset;
use App\Console\Commands\ScheduleLunchReminder;
use App\Http\Controllers\LunchController;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Scheduling commands dynamically from lunch_schedules table
        $this->app->booted(function () {
            if (Schema::hasTable('lunch_schedules')) {
                $schedule = app(Schedule::class);
                $settings = \App\Models\LunchSchedule::first();

                if ($settings && $settings->enabled) {
                    if ($settings->announce_time) {
                        $schedule->command(AnnounceLunch::class)->dailyAt($settings->announce_time);
                    }

                    if ($settings->reminder_time) {
                        $schedule->command(ScheduleLunchReminder::class)->dailyAt($settings->reminder_time);
                    }

                    if ($settings->reset_time) {
                        $schedule->command(LunchReset::class)->dailyAt($settings->reset_time);
                    }

                    if ($settings->approval_time) {
                        $schedule->command(ApproveLunch::class)->dailyAt($settings->approval_time);
                    }
                }
            }
        });

//        $this->app->resolving(Nutgram::class, function (Nutgram $bot) {
//            \Log::info('[Nutgram] Registering handlers...');
//
//            $bot->onCommand('start', [LunchController::class, 'handleStart']);
//            $bot->onCommand('announce_lunch', [LunchController::class, 'announceLunch']);
//            $bot->onCallbackQuery('lunch_request', [LunchController::class, 'handleLunchRequest']);
//            $bot->onCommand('process_lunch_list', [LunchController::class, 'processLunchList']);
//            $bot->onCommand('schedule_lunch_reminders', [LunchController::class, 'scheduleReminders']);
//            $bot->onCommand('approve_lunch', [LunchController::class, 'approveLunch']);
//            $bot->onCommand('lunch_status', [LunchController::class, 'lunchStatus']);
//            $bot->onCommand('reset_lunch', [LunchController::class, 'resetLunch']);
//        });
    }
}
