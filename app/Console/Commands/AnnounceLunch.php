<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\LunchController;
use SergiX44\Nutgram\Nutgram;
use Illuminate\Console\Scheduling\Schedule;
use Laravel\Prompts\info;

class AnnounceLunch extends Command
{
    protected $signature = 'lunch:announce';
    protected $description = 'Send lunch announcement to the group.';

    public function handle(): void
    {
        $bot = app(Nutgram::class);
        app(LunchController::class)->announceLunch($bot);

        info('Lunch announcement sent.');
    }

    public function schedule(Schedule $schedule): void
    {
        $schedule->dailyAt('12:40');
    }
}

