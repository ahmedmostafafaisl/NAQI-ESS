<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'roles.manage',
            'permissions.manage',
            'notifications.view',
            'notifications.send',
            'leaves.view',
            'leaves.approve',
            'attendance.view',
            'payslips.view',
            'payslips.publish',
            'dynamics.sync',
            'settings.view',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'users.view',
            'users.create',
            'users.edit',
            'notifications.view',
            'notifications.send',
            'leaves.view',
            'leaves.approve',
            'attendance.view',
            'payslips.view',
            'payslips.publish',
            'settings.view',
        ]);

        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
    }
}
