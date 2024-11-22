<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserDevice;

class UserDeviceSeeder extends Seeder
{
    public function run()
    {
        $users = User::where('role', 'user')->get();

        foreach ($users as $user) {

            $devices = [
                [
                    'device_type' => 'web',
                    'device_name' => 'Chrome Browser',
                    'device_info' => [
                        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0.4472.124',
                        'ip' => '192.168.1.1'
                    ]
                ],
                [
                    'device_type' => 'mobile',
                    'device_name' => 'iPhone App',
                    'device_info' => [
                        'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6)',
                        'ip' => '192.168.1.2'
                    ]
                ]
            ];

            foreach ($devices as $device) {
                UserDevice::create([
                    'user_id' => $user->id,
                    'device_id' => hash('sha256', $user->id . $device['device_type'] . rand()),
                    'device_type' => $device['device_type'],
                    'device_name' => $device['device_name'],
                    'device_info' => $device['device_info'],
                    'registered_at' => now()->subDays(rand(1, 30)),
                    'last_access_at' => now()->subHours(rand(1, 24)),
                    'is_active' => true
                ]);
            }
        }
    }
}
