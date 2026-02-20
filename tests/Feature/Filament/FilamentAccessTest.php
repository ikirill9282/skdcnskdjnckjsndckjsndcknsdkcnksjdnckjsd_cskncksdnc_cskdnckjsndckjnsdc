<?php

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('user', 'web');
        Role::findOrCreate('company-admin', 'web');
        Role::findOrCreate('manager', 'web');
        Role::findOrCreate('client', 'web');
    }

    public function test_guest_is_redirected_from_admin_panel(): void
    {
        $response = $this->get('/admin9282');

        $response->assertRedirect();
    }

    public function test_super_admin_can_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get('/admin9282');

        $response->assertOk();
    }

    public function test_admin_can_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $response = $this->actingAs($user)->get('/admin9282');

        $response->assertOk();
    }

    public function test_company_admin_can_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('company-admin');

        $response = $this->actingAs($user)->get('/admin9282');

        $response->assertOk();
    }

    public function test_manager_can_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $response = $this->actingAs($user)->get('/admin9282');

        $response->assertOk();
    }

    public function test_client_can_access_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->assignRole('client');

        $response = $this->actingAs($user)->get('/admin9282');

        $response->assertOk();
    }

    public function test_user_without_role_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin9282');

        // Filament returns 403 for unauthorized users
        $this->assertTrue(in_array($response->status(), [302, 403]));
    }

    public function test_super_admin_can_access_users_list(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get('/admin9282/users');

        $response->assertOk();
    }

    public function test_super_admin_can_access_user_create_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get('/admin9282/users/create');

        $response->assertOk();
    }

    public function test_super_admin_can_access_companies_list(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get('/admin9282/companies');

        $response->assertOk();
    }

    public function test_super_admin_can_access_stations_list(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get('/admin9282/stations');

        $response->assertOk();
    }
}
