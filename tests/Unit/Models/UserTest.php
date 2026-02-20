<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'name' => 'Test',
            'email' => 'test@test.com',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@test.com',
            'name' => 'Test',
        ]);
    }

    public function test_user_belongs_to_many_companies(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $user->companies()->attach($company);

        $this->assertTrue($user->companies->contains($company));
        $this->assertCount(1, $user->companies);
    }

    public function test_user_belongs_to_many_stations(): void
    {
        $user = User::factory()->create();
        $station = Station::factory()->create();

        $user->stations()->attach($station);

        $this->assertTrue($user->stations->contains($station));
    }

    public function test_user_can_have_roles(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('super-admin', 'web');

        $user->assignRole('super-admin');

        $this->assertTrue($user->hasRole('super-admin'));
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $user->password);
    }

    public function test_can_access_panel_with_valid_role(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('super-admin', 'web');
        $user->assignRole('super-admin');

        $panel = \Filament\Facades\Filament::getPanel('admin9282');
        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_cannot_access_panel_without_role(): void
    {
        $user = User::factory()->create();

        $panel = \Filament\Facades\Filament::getPanel('admin9282');
        $this->assertFalse($user->canAccessPanel($panel));
    }
}
