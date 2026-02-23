<?php

namespace Tests\Feature\Filament;

use App\Models\Company;
use App\Models\Station;
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

    public function test_super_admin_can_access_station_create_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $response = $this->actingAs($user)->get('/admin9282/stations/create');

        $response->assertOk();
    }

    public function test_admin_cannot_access_station_create_page(): void
    {
        $company = Company::factory()->create();
        $user = $this->createBusinessAdmin('admin', $company);

        $response = $this->actingAs($user)->get('/admin9282/stations/create');

        $response->assertForbidden();
    }

    public function test_company_admin_cannot_access_station_create_page(): void
    {
        $company = Company::factory()->create();
        $user = $this->createBusinessAdmin('company-admin', $company);

        $response = $this->actingAs($user)->get('/admin9282/stations/create');

        $response->assertForbidden();
    }

    public function test_business_admin_sees_only_stations_from_assigned_companies(): void
    {
        $companyA = Company::factory()->create(['name' => 'Allowed Company']);
        $companyB = Company::factory()->create(['name' => 'Forbidden Company']);

        $stationAllowed = Station::factory()->create([
            'company_id' => $companyA->id,
            'name' => 'Allowed Station',
        ]);
        $stationForbidden = Station::factory()->create([
            'company_id' => $companyB->id,
            'name' => 'Forbidden Station',
        ]);

        $user = $this->createBusinessAdmin('admin', $companyA);

        $response = $this->actingAs($user)->get('/admin9282/stations');

        $response->assertOk();
        $response->assertSee($stationAllowed->name);
        $response->assertDontSee($stationForbidden->name);
    }

    public function test_manager_can_open_station_status_but_not_parameters(): void
    {
        $company = Company::factory()->create();
        $station = Station::factory()->create(['company_id' => $company->id]);
        $manager = User::factory()->create();
        $manager->assignRole('manager');
        $manager->companies()->attach($company);
        $manager->stations()->attach($station);

        $statusResponse = $this->actingAs($manager)->get("/admin9282/stations/{$station->id}/status");
        $statusResponse->assertOk();

        $parametersResponse = $this->actingAs($manager)->get("/admin9282/stations/{$station->id}/parameters");
        $parametersResponse->assertForbidden();
    }

    public function test_client_can_open_only_station_statistics_page(): void
    {
        $company = Company::factory()->create();
        $station = Station::factory()->create(['company_id' => $company->id]);
        $client = User::factory()->create();
        $client->assignRole('client');
        $client->companies()->attach($company);
        $client->stations()->attach($station);

        $statisticsResponse = $this->actingAs($client)->get("/admin9282/stations/{$station->id}/statistics");
        $statisticsResponse->assertOk();

        $statusResponse = $this->actingAs($client)->get("/admin9282/stations/{$station->id}/status");
        $statusResponse->assertForbidden();

        $editResponse = $this->actingAs($client)->get("/admin9282/stations/{$station->id}/edit");
        $editResponse->assertForbidden();

        $parametersResponse = $this->actingAs($client)->get("/admin9282/stations/{$station->id}/parameters");
        $parametersResponse->assertForbidden();
    }

    public function test_admin_and_company_admin_can_access_users_list(): void
    {
        $company = Company::factory()->create();
        $admin = $this->createBusinessAdmin('admin', $company);
        $companyAdmin = $this->createBusinessAdmin('company-admin', $company);

        $adminResponse = $this->actingAs($admin)->get('/admin9282/users');
        $adminResponse->assertOk();

        $companyAdminResponse = $this->actingAs($companyAdmin)->get('/admin9282/users');
        $companyAdminResponse->assertOk();
    }

    public function test_manager_and_client_cannot_access_users_list(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('manager');

        $client = User::factory()->create();
        $client->assignRole('client');

        $managerResponse = $this->actingAs($manager)->get('/admin9282/users');
        $managerResponse->assertForbidden();

        $clientResponse = $this->actingAs($client)->get('/admin9282/users');
        $clientResponse->assertForbidden();
    }

    public function test_business_admin_cannot_edit_privileged_user(): void
    {
        $company = Company::factory()->create();
        $adminActor = $this->createBusinessAdmin('admin', $company);

        $privilegedUser = User::factory()->create();
        $privilegedUser->assignRole('company-admin');
        $privilegedUser->companies()->attach($company);

        $response = $this->actingAs($adminActor)->get("/admin9282/users/{$privilegedUser->id}/edit");

        $this->assertForbiddenOrNotFound($response->status());
    }

    public function test_business_admin_without_company_cannot_access_companies_stations_and_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $companiesResponse = $this->actingAs($user)->get('/admin9282/companies');
        $stationsResponse = $this->actingAs($user)->get('/admin9282/stations');
        $usersResponse = $this->actingAs($user)->get('/admin9282/users');

        $this->assertForbiddenOrNotFound($companiesResponse->status());
        $this->assertForbiddenOrNotFound($stationsResponse->status());
        $this->assertForbiddenOrNotFound($usersResponse->status());
    }

    private function createBusinessAdmin(string $role, Company $company): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);
        $user->companies()->attach($company);

        return $user;
    }

    private function assertForbiddenOrNotFound(int $statusCode): void
    {
        $this->assertTrue(
            in_array($statusCode, [403, 404], true),
            "Expected 403 or 404 status code, got {$statusCode}."
        );
    }
}
