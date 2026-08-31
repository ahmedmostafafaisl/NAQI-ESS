<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DynamicsUser extends Model
{
    protected $fillable = ['email', 'mobile', 'password', 'device_token', 'device_id', 'otp', 'otp_expires_at'];

    protected $hidden = ['password', 'otp'];

    protected function casts(): array
    {
        return [
            'otp_expires_at' => 'datetime',
            // 'password' => 'encrypted',
        ];
    }
}
