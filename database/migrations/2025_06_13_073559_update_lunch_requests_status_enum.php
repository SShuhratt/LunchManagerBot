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
        Schema::table('lunch_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('lunch_requests', function (Blueprint $table) {
            $table->enum('status', ['available', 'requested', 'at_lunch'])->default('available');
            $table->unique('user_id'); // Ensure one record per user
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
