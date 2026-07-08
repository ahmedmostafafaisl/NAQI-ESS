<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $guard_name = 'web'; // shared guard for roles/permissions (web + api)

    protected $fillable = [
        'tech_id',
        'username',
        'email',
        'phone',
        'pin_code',
        'password',
        'type',
        'image',
        'status',
        'fcm_token',
        'personnel_number',
        'dynamics_id',
        'dynamics_synced_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
        'pin_code',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'dynamics_synced_at' => 'datetime',
            'password' => 'hashed',
            'pin_code' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->type === 'employee' && $this->hasAnyRole(['super-admin', 'admin']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getImageUrlAttribute(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}
