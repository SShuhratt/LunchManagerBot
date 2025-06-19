<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\LunchRequest;
use Laravel\Prompts\info;
use Illuminate\Console\Scheduling\Schedule;

class LunchReset extends Command
{
    protected $signature = 'lunch:reset';
    protected $description = 'Reset lunch status for all users';

    public function handle(): void
    {
        LunchRequest::query()->update(['status' => 'available']);
        info('All lunch statuses have been reset.');
    }
}
