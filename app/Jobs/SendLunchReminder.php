<?php

namespace App\Jobs;

use SergiX44\Nutgram\Nutgram;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendLunchReminder implements ShouldQueue
{
    use Queueable;

    protected $userId;
    protected $message;

    public function __construct($userId, $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    public function handle()
    {
        $bot = new Nutgram(env('TELEGRAM_BOT_TOKEN'));
        $bot->sendMessage($this->userId, $this->message);
    }
}
