<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class DynamicsUser extends Model
{
    protected $fillable = ['email', 'password', 'device_token'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            // 'password' => 'encrypted',
        ];
    }
}
