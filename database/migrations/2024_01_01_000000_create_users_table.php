<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('tech_id')->nullable();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('pin_code')->nullable();
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->enum('type', ['employee', 'customer'])->default('employee');
            $table->string('image')->nullable();
            $table->string('role')->nullable(); // quick-access label, real authorization via spatie roles/permissions
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('fcm_token')->nullable();

            // Additional fields for user management
            $table->string('personnel_number')->nullable();

            // Dynamics 365 linkage
            $table->string('dynamics_id')->nullable()->comment('GUID of the related record in Dynamics 365');
            $table->timestamp('dynamics_synced_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Indexes must come after the columns they reference are defined
            $table->index(['type', 'phone'], 'idx_type_phone');
            $table->index(['type', 'username'], 'idx_type_username');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
