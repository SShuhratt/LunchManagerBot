<?php

namespace App\Providers;

use App\Console\Commands\AnnounceLunch;
use App\Console\Commands\ApproveLunch;
use App\Console\Commands\LunchReset;
use App\Console\Commands\ScheduleLunchReminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;

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
            $schedule = app(Schedule::class);
            $schedule->command(AnnounceLunch::class)->dailyAt('12:45');
            $schedule->command(ApproveLunch::class)->dailyAt('12:55');
            $schedule->command(LunchReset::class)->dailyAt('08:00');
            $schedule->command(ScheduleLunchReminder::class)->dailyAt('13:00');
        });
    }
}
