<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_active',
        'max_devices'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'max_devices' => 'integer'
    ];

    public function devices()
    {
        return $this->hasMany(UserDevice::class);
    }

    public function activeDevices()
    {
        return $this->devices()->where('is_active', true);
    }

    public function canAddDevice($deviceType)
    {
        $active_devices_count = $this->activeDevices()->count();
        $has_device_type = $this->activeDevices()
            ->where('device_type', $deviceType)
            ->exists();

        return $active_devices_count < $this->max_devices && !$has_device_type;
    }
}