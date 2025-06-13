<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SergiX44\Nutgram\Nutgram;

class SendLunchReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $message)
    {
        $this->userId = $userId;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $bot = app(Nutgram::class);
            $bot->sendMessage($this->userId, $this->message);
        } catch (\Exception $e) {
            // Log the error but don't fail the job
            \Log::error("Failed to send lunch reminder to user {$this->userId}: " . $e->getMessage());
        }
    }
}
