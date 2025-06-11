<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LunchRequest extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'user_id', 'is_supervisor', 'status', 'lunch_time'];
}
