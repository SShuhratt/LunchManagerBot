<?php

namespace App\Providers;

use App\Console\Commands\AnnounceLunch;
use App\Console\Commands\ApproveLunch;
use App\Console\Commands\LunchReset;
use App\Console\Commands\ScheduleLunchReminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

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
        $this->app->booted(function () {
            if(Schema::hasTable('lunch_schedules')) {
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
    }
}
