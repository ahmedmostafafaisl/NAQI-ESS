<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamics_users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->text('password');
            $table->string('device_token', 500)->nullable();
            $table->string('device_id')->nullable();
            $table->string('mobile')->nullable();
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamps();
            $table->index('device_token', 'idx_dynamics_users_device_token');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamics_users');
    }
};
