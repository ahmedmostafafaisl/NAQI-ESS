<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_root_to_admin_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_login_page_loads(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertViewIs('auth.login');
    }

    public function test_non_admin_user_cannot_access_dashboard(): void
    {
        Role::firstOrCreate(['name' => 'employee', 'guard_name' => 'web']);

        $user = User::factory()->create(['password' => bcrypt('password')]);
        $user->assignRole('employee');

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_user_can_login_and_reach_dashboard(): void
    {
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $admin = User::factory()->create(['password' => bcrypt('secret123')]);
        $admin->assignRole('admin');

        $response = $this->post('/admin/login', [
            'login' => $admin->email,
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }
}
