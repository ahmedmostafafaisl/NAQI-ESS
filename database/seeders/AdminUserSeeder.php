<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@naqi-ess.test'],
            [
                'username' => 'Super Admin',
                'phone' => '0500000000',
                'password' => Hash::make('Password@123'),
                'type' => 'employee',
                'status' => 'active',
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('super-admin');
    }
}
