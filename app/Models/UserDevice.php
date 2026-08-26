<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One device belonging to one user. Deliberately separate from
 * User::fcm_token (the older single-token column, still used by the
 * existing admin broadcast system) — this table is what powers multi-device
 * push (registering multiple devices per user, and notifying "all devices
 * except this one" on events like a new device verifying OTP).
 */
class UserDevice extends Model
{
    protected $fillable = ['user_id', 'device_id', 'fcm_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
