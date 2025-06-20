<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lunch_schedules', function (Blueprint $table) {
            $table->id();
            $table->time('announce_time')->default('12:45');
            $table->time('approval_time')->default('12:55');
            $table->time('reminder_time')->default('13:00');
            $table->time('reset_time')->default('08:00');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lunch_schedules');
    }
};
