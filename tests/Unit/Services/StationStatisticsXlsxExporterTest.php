<?php

namespace Tests\Unit\Services;

use App\Services\Statistics\StationStatisticsXlsxExporter;
use Carbon\Carbon;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class StationStatisticsXlsxExporterTest extends TestCase
{
    public function test_it_creates_xlsx_with_expected_sections(): void
    {
        $report = $this->makeReport();

        $export = app(StationStatisticsXlsxExporter::class)->export(
            $report,
            'ST-555',
            Carbon::parse('2026-02-01'),
            Carbon::parse('2026-02-10'),
        );

        $this->assertFileExists($export['path']);
        $this->assertStringEndsWith('.xlsx', $export['filename']);

        $reader = new Reader();
        $reader->open($export['path']);

        $values = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                foreach ($row->toArray() as $cellValue) {
                    $values[] = (string) $cellValue;
                }
            }
            break;
        }

        $reader->close();

        $this->assertContains('выбор периода', $values);
        $this->assertContains('Моющие средства', $values);
        $this->assertContains('Программы', $values);
        $this->assertContains('Количество оконченных программ', $values);
        $this->assertContains('POSTEL 60', $values);

        @unlink($export['path']);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeReport(): array
    {
        $detergents = [];
        for ($i = 1; $i <= 8; $i++) {
            $detergents[$i] = [
                'number' => $i,
                'name' => "det {$i}",
                'liters' => (float) $i,
            ];
        }

        $machines = [];
        for ($i = 1; $i <= 6; $i++) {
            $machines[$i] = [
                'number' => $i,
                'name' => $i <= 3 ? 'Haier' : 'IMAGE',
                'loading' => $i <= 3 ? 10.0 : 15.0,
                'total_kg' => (float) ($i * 10),
                'completed_total' => $i,
            ];
        }

        $programs = [];
        for ($i = 1; $i <= 19; $i++) {
            $programs[$i] = [
                'number' => $i,
                'name' => $i === 1 ? 'POSTEL 60' : "Программа {$i}",
                'interrupted_total' => $i === 1 ? 2 : 0,
                'completed_total' => $i === 1 ? 18 : 0,
                'total_kg' => $i === 1 ? 270.0 : 0.0,
                'completed_by_machine' => [
                    1 => $i === 1 ? 0 : 0,
                    2 => $i === 1 ? 0 : 0,
                    3 => $i === 1 ? 0 : 0,
                    4 => $i === 1 ? 3 : 0,
                    5 => $i === 1 ? 7 : 0,
                    6 => $i === 1 ? 8 : 0,
                ],
            ];
        }

        return [
            'period' => [
                'start' => Carbon::parse('2026-02-01')->startOfDay(),
                'end' => Carbon::parse('2026-02-10')->endOfDay(),
                'start_parts' => ['day' => 1, 'month' => 2, 'year' => 2026],
                'end_parts' => ['day' => 10, 'month' => 2, 'year' => 2026],
            ],
            'detergents' => $detergents,
            'machines' => $machines,
            'programs' => $programs,
            'totals' => [
                'liters_total' => 36.0,
                'kg_total' => 270.0,
                'machine_kg_total' => 210.0,
            ],
        ];
    }
}
