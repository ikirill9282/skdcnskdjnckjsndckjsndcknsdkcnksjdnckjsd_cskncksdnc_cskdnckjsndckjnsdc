<?php

namespace Tests\Unit\Services;

use App\Models\Company;
use App\Models\Program;
use App\Models\Station;
use App\Models\StationLog;
use App\Models\Statistic;
use App\Services\Statistics\StationPeriodStatisticsReportBuilder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StationPeriodStatisticsReportBuilderTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_builds_aggregates_for_selected_period_and_ignores_invalid_logs(): void
    {
        $company = Company::factory()->create();

        $station = Station::factory()->create([
            'company_id' => $company->id,
            'detergents_data' => [
                ['name' => 'det 1'],
                ['name' => 'det 2'],
            ],
            'machines_data' => [
                ['name' => 'Haier', 'loading' => 10],
                ['name' => 'IMAGE', 'loading' => 15],
            ],
        ]);

        Program::create([
            'station_id' => $station->id,
            'program_number' => 1,
            'name' => 'POSTEL 60',
        ]);

        Program::create([
            'station_id' => $station->id,
            'program_number' => 2,
            'name' => 'POSTEL 40',
        ]);

        Statistic::create([
            'station_id' => $station->id,
            'date' => '2026-02-01',
            'data' => [
                'column_1' => 10,
                'column_2' => 5,
                'liters_1' => 1.2,
                'liters_2' => 0.8,
                'kg_1' => 20,
                'kg_2' => 10,
            ],
        ]);

        Statistic::create([
            'station_id' => $station->id,
            'date' => '2026-02-02',
            'data' => [
                'column_1' => 2,
                'column_2' => 3,
                'liters_1' => 1.0,
                'liters_2' => 0.5,
                'kg_1' => 4,
                'kg_2' => 6,
            ],
        ]);

        Statistic::create([
            'station_id' => $station->id,
            'date' => '2026-03-01',
            'data' => [
                'column_1' => 100,
                'liters_1' => 100,
                'kg_1' => 100,
            ],
        ]);

        $this->createLog($station, 'done', 1, 1, '2026-02-01 10:00:00');
        $this->createLog($station, 'done', 2, 1, '2026-02-01 11:00:00');
        $this->createLog($station, 'stop', 1, 1, '2026-02-01 12:00:00');
        $this->createLog($station, 'error', 2, 2, '2026-02-02 09:00:00');
        $this->createLog($station, 'done', 1, 20, '2026-02-02 09:10:00'); // invalid program
        $this->createLog($station, 'done', null, 1, '2026-02-02 09:20:00'); // invalid machine
        $this->createLog($station, 'running', 1, 1, '2026-02-02 09:30:00'); // ignored event type
        $this->createLog($station, 'done', 1, 1, '2026-03-10 10:00:00'); // out of period

        $report = app(StationPeriodStatisticsReportBuilder::class)->build(
            $station,
            Carbon::parse('2026-02-01'),
            Carbon::parse('2026-02-28'),
        );

        $this->assertSame('POSTEL 60', $report['programs'][1]['name']);
        $this->assertSame('POSTEL 40', $report['programs'][2]['name']);

        $this->assertEquals(2.2, $report['detergents'][1]['liters']);
        $this->assertEquals(1.3, $report['detergents'][2]['liters']);
        $this->assertEquals(24.0, $report['machines'][1]['total_kg']);
        $this->assertEquals(16.0, $report['machines'][2]['total_kg']);
        $this->assertEquals(12.0, $report['programs'][1]['total_kg']);
        $this->assertEquals(8.0, $report['programs'][2]['total_kg']);
        $this->assertEquals(20.0, $report['totals']['kg_total']);

        $this->assertSame(2, $report['programs'][1]['completed_total']);
        $this->assertSame(1, $report['programs'][1]['interrupted_total']);
        $this->assertSame(0, $report['programs'][2]['completed_total']);
        $this->assertSame(1, $report['programs'][2]['interrupted_total']);
        $this->assertSame(1, $report['programs'][1]['completed_by_machine'][1]);
        $this->assertSame(1, $report['programs'][1]['completed_by_machine'][2]);
        $this->assertSame(1, $report['machines'][1]['completed_total']);
        $this->assertSame(1, $report['machines'][2]['completed_total']);
    }

    private function createLog(
        Station $station,
        string $eventType,
        ?int $machineNumber,
        ?int $programNumber,
        string $occurredAt,
    ): void {
        $log = StationLog::create([
            'station_id' => $station->id,
            'event_type' => $eventType,
            'washing_machine_number' => $machineNumber,
            'program_number' => $programNumber,
        ]);

        $log->forceFill([
            'created_at' => Carbon::parse($occurredAt),
            'updated_at' => Carbon::parse($occurredAt),
        ])->save();
    }
}
