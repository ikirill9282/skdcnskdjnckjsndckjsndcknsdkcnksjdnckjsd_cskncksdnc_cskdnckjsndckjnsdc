<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\StationResource\Pages\StationStatistics;
use App\Models\Company;
use App\Models\Station;
use App\Models\StationLog;
use App\Models\Statistic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StationStatisticsExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('company-admin', 'web');
        Role::findOrCreate('manager', 'web');
        Role::findOrCreate('client', 'web');
    }

    public function test_super_admin_can_export_station_statistics_xlsx(): void
    {
        $station = $this->createStationWithStatistics();

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        Livewire::actingAs($user)
            ->test(StationStatistics::class, ['record' => (string) $station->id])
            ->set('startDate', '2026-02-01')
            ->set('endDate', '2026-02-01')
            ->call('exportPeriodXlsx')
            ->assertFileDownloaded('station-st-100-statistics-2026-02-01_2026-02-01.xlsx');
    }

    public function test_client_with_station_access_can_export_station_statistics_xlsx(): void
    {
        $station = $this->createStationWithStatistics();

        $client = User::factory()->create();
        $client->assignRole('client');
        $client->companies()->attach($station->company_id);
        $client->stations()->attach($station->id);

        Livewire::actingAs($client)
            ->test(StationStatistics::class, ['record' => (string) $station->id])
            ->set('startDate', '2026-02-01')
            ->set('endDate', '2026-02-01')
            ->call('exportPeriodXlsx')
            ->assertFileDownloaded('station-st-100-statistics-2026-02-01_2026-02-01.xlsx');
    }

    public function test_export_does_not_start_when_period_is_invalid(): void
    {
        $station = $this->createStationWithStatistics();

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        Livewire::actingAs($user)
            ->test(StationStatistics::class, ['record' => (string) $station->id])
            ->set('startDate', '2026-02-10')
            ->set('endDate', '2026-02-01')
            ->call('exportPeriodXlsx')
            ->assertNoFileDownloaded();
    }

    private function createStationWithStatistics(): Station
    {
        $company = Company::factory()->create();

        $station = Station::factory()->create([
            'company_id' => $company->id,
            'code' => 'ST-100',
            'detergents_data' => [
                ['name' => 'det 1'],
                ['name' => 'det 2'],
            ],
            'machines_data' => [
                ['name' => 'Haier', 'loading' => 10],
                ['name' => 'IMAGE', 'loading' => 15],
            ],
        ]);

        Statistic::create([
            'station_id' => $station->id,
            'date' => '2026-02-01',
            'data' => [
                'column_1' => 25,
                'liters_1' => 2.2,
                'liters_2' => 1.8,
                'kg_1' => 20,
                'kg_2' => 30,
            ],
        ]);

        StationLog::create([
            'station_id' => $station->id,
            'event_type' => 'done',
            'washing_machine_number' => 1,
            'program_number' => 1,
        ]);

        return $station;
    }
}
