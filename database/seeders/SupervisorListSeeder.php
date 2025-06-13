<?php

namespace Database\Seeders;

use App\Models\SupervisorList;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupervisorListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SupervisorList::create([
            'user_id' => 123456789, // Replace with actual Telegram user IDs
            'name' => 'John Supervisor',
            'department' => 'Management'
        ]);
    }
}
