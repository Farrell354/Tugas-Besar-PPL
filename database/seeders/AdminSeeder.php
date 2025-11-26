<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'], // Kunci pencarian
            [
                'name' => 'Administrator',
                'password' => Hash::make('12345678'), // Password
                'role' => 'admin',
            ]
        );
    }
}
