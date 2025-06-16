<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LunchSchedule extends Model
{
    protected $fillable = [
        'announce_time',
        'approval_time',
        'reminder_time',
        'reset_time',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'announce_time' => 'datetime:H:i',
        'approval_time' => 'datetime:H:i',
        'reminder_time' => 'datetime:H:i',
        'reset_time' => 'datetime:H:i',
    ];
}
