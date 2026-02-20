<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\Station;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_can_be_created(): void
    {
        $company = Company::factory()->create(['name' => 'Test Company']);

        $this->assertDatabaseHas('companies', ['name' => 'Test Company']);
    }

    public function test_company_has_many_stations(): void
    {
        $company = Company::factory()->create();
        Station::factory()->count(3)->create(['company_id' => $company->id]);

        $this->assertCount(3, $company->stations);
    }

    public function test_company_belongs_to_many_users(): void
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();

        $company->users()->attach($user);

        $this->assertTrue($company->users->contains($user));
    }
}
