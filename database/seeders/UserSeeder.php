<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'ahmed@example.com'],
            [
                'name' => 'Ahmed Elsayed',
                'password' => Hash::make('password'),
                'role' => 'customer',
            ]
        );

        User::factory()->count(50)->create();
    }
}
