<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key' => 'notify_old_devices_on_login',
            'value' => 'true',
            'type' => 'boolean',
            'description' => 'When a new device verifies OTP, notify (and signal log-out to) the 5 most recently active other devices.',
            'is_public' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'notify_old_devices_on_login')->delete();
    }
};
