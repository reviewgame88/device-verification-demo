<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'device_type',
        'device_name',
        'device_info',
        'last_access_at',
        'registered_at',
        'is_active'
    ];

    protected $casts = [
        'device_info' => 'array',
        'last_access_at' => 'datetime',
        'registered_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
