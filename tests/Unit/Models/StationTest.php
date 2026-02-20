<?php

namespace Tests\Unit\Models;

use App\Models\Company;
use App\Models\Program;
use App\Models\Station;
use App\Models\StationLog;
use App\Models\StationSettingBlockUpdate;
use App\Models\StationSettingValue;
use App\Models\Statistic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationTest extends TestCase
{
    use RefreshDatabase;

    public function test_station_can_be_created(): void
    {
        $station = Station::factory()->create([
            'code' => 'TEST-001',
            'name' => 'Test Station',
        ]);

        $this->assertDatabaseHas('stations', [
            'code' => 'TEST-001',
            'name' => 'Test Station',
        ]);
    }

    public function test_station_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $station = Station::factory()->create(['company_id' => $company->id]);

        $this->assertEquals($company->id, $station->company->id);
    }

    public function test_station_belongs_to_many_users(): void
    {
        $station = Station::factory()->create();
        $user = User::factory()->create();

        $station->users()->attach($user);

        $this->assertTrue($station->users->contains($user));
    }

    public function test_station_has_many_logs(): void
    {
        $station = Station::factory()->create();
        StationLog::factory()->count(5)->create(['station_id' => $station->id]);

        $this->assertCount(5, $station->logs);
    }

    public function test_station_has_many_programs(): void
    {
        $station = Station::factory()->create();
        Program::factory()->create(['station_id' => $station->id, 'program_number' => 1]);
        Program::factory()->create(['station_id' => $station->id, 'program_number' => 2]);

        $this->assertCount(2, $station->programs);
    }

    public function test_station_has_many_statistics(): void
    {
        $station = Station::factory()->create();
        Statistic::factory()->create(['station_id' => $station->id]);

        $this->assertCount(1, $station->statistics);
    }

    public function test_station_has_many_setting_values(): void
    {
        $station = Station::factory()->create();
        StationSettingValue::create([
            'station_id' => $station->id,
            'block_number' => 306,
            'setting_index' => 1,
            'value' => 'test',
        ]);

        $this->assertCount(1, $station->settingValues);
    }

    public function test_station_has_many_setting_block_updates(): void
    {
        $station = Station::factory()->create();
        StationSettingBlockUpdate::create([
            'station_id' => $station->id,
            'block_number' => 306,
            'changed_by' => 'admin',
        ]);

        $this->assertCount(1, $station->settingBlockUpdates);
    }

    public function test_station_casts_json_fields(): void
    {
        $station = Station::factory()->create([
            'machines_data' => [['name' => 'M1', 'active' => true]],
            'is_active' => true,
        ]);

        $station->refresh();

        $this->assertIsArray($station->machines_data);
        $this->assertIsBool($station->is_active);
    }
}
