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
            'user_id' => 1971976188,
            'name' => 'Shuhrat',
        ]);
    }
}
