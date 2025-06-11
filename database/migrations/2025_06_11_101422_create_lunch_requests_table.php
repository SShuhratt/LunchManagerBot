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
        Schema::create('lunch_requests', function (Blueprint $table) {
            $table->id();  // Unique identifier
            $table->string('name');  // User's name
            $table->unsignedBigInteger('user_id');  // Telegram user ID
            $table->boolean('is_supervisor')->default(false);  // Role management via Spatie
            $table->enum('status', ['at_lunch', 'work', 'dayoff'])->default('work');  // User's current status
            $table->timestamp('lunch_time')->nullable();  // Scheduled lunch start time
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lunch_requests');
    }
};
