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
}
