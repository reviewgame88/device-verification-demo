<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'phone' => '0123456789',
            'is_active' => true,
            'email_verified_at' => now(),
            'max_devices' => 5 // Admin gets more devices
        ]);

        // Create regular users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '0987654321',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '0123498765',
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'phone' => '0123789456',
            ]
        ];

        foreach ($users as $user) {
            User::create(array_merge($user, [
                'password' => Hash::make('user123'),
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => now(),
                'max_devices' => 3
            ]));
        }
    }
}
