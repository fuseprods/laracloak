<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Base Administrator
        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => env('INITIAL_ADMIN_EMAIL', 'admin@laracloak.com'),
            'password' => \Illuminate\Support\Facades\Hash::make(env('INITIAL_ADMIN_PASSWORD', 'password')),
            'role' => 'admin',
        ]);

        // 2. Base Group
        \App\Models\Group::create([
            'name' => 'Analyst',
            'description' => 'Group with general view permissions'
        ]);

        // 3. Example Category
        \App\Models\Category::create([
            'name' => 'General',
            'description' => 'General pages'
        ]);
    }
}
