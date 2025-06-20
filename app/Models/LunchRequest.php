<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class LunchRequest extends Model
{
    use HasFactory, HasApiTokens;

    protected $fillable = ['name', 'user_id', 'is_supervisor', 'status', 'lunch_time'];
}
