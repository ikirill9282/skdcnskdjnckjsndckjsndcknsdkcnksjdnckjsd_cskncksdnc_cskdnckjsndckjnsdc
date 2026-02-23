<?php

namespace App\Services\Statistics;

use App\Models\Program;
use App\Models\Station;
use App\Models\StationLog;
use App\Models\Statistic;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StationPeriodStatisticsReportBuilder
{
    private const PROGRAM_COUNT = 19;

    private const DETERGENT_COUNT = 8;

    private const MACHINE_COUNT = 6;

    /**
     * @return array<string, mixed>
     */
    public function build(Station $station, Carbon $start, Carbon $end): array
    {
        $statistics = Statistic::query()
            ->where('station_id', $station->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get(['data']);

        $programKg = $this->sumStatisticValues($statistics, 'column_', self::PROGRAM_COUNT);
        $liters = $this->sumStatisticValues($statistics, 'liters_', self::DETERGENT_COUNT);
        $machineKg = $this->sumStatisticValues($statistics, 'kg_', self::MACHINE_COUNT);

        $completedByProgram = array_fill(1, self::PROGRAM_COUNT, 0);
        $interruptedByProgram = array_fill(1, self::PROGRAM_COUNT, 0);
        $completedByMachine = array_fill(1, self::MACHINE_COUNT, 0);
        $completedByProgramMachine = [];

        for ($program = 1; $program <= self::PROGRAM_COUNT; $program++) {
            $completedByProgramMachine[$program] = array_fill(1, self::MACHINE_COUNT, 0);
        }

        $logs = StationLog::query()
            ->where('station_id', $station->id)
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->get(['event_type', 'program_number', 'washing_machine_number']);

        foreach ($logs as $log) {
            $programNumber = (int) ($log->program_number ?? 0);
            $machineNumber = (int) ($log->washing_machine_number ?? 0);

            if (! $this->isValidIndex($programNumber, self::PROGRAM_COUNT) || ! $this->isValidIndex($machineNumber, self::MACHINE_COUNT)) {
                continue;
            }

            $eventType = Str::lower(trim((string) $log->event_type));

            if ($eventType === 'done') {
                $completedByProgram[$programNumber]++;
                $completedByProgramMachine[$programNumber][$machineNumber]++;
                $completedByMachine[$machineNumber]++;
                continue;
            }

            if (in_array($eventType, ['stop', 'error'], true)) {
                $interruptedByProgram[$programNumber]++;
            }
        }

        $programNames = Program::query()
            ->where('station_id', $station->id)
            ->pluck('name', 'program_number')
            ->all();

        $detergentsData = is_array($station->detergents_data) ? $station->detergents_data : [];
        $machinesData = is_array($station->machines_data) ? $station->machines_data : [];

        $detergents = [];
        for ($i = 1; $i <= self::DETERGENT_COUNT; $i++) {
            $name = trim((string) data_get($detergentsData, ($i - 1) . '.name', ''));
            if ($name === '') {
                $name = "Средство {$i}";
            }

            $detergents[$i] = [
                'number' => $i,
                'name' => $name,
                'liters' => $liters[$i],
            ];
        }

        $machines = [];
        for ($i = 1; $i <= self::MACHINE_COUNT; $i++) {
            $name = trim((string) data_get($machinesData, ($i - 1) . '.name', ''));
            if ($name === '') {
                $name = "Машина {$i}";
            }

            $machines[$i] = [
                'number' => $i,
                'name' => $name,
                'loading' => $this->toFloat(data_get($machinesData, ($i - 1) . '.loading', 0)),
                'total_kg' => $machineKg[$i],
                'completed_total' => $completedByMachine[$i],
            ];
        }

        $programs = [];
        for ($i = 1; $i <= self::PROGRAM_COUNT; $i++) {
            $name = trim((string) ($programNames[$i] ?? ''));
            if ($name === '') {
                $name = "Программа {$i}";
            }

            $programs[$i] = [
                'number' => $i,
                'name' => $name,
                'interrupted_total' => $interruptedByProgram[$i],
                'completed_total' => $completedByProgram[$i],
                'total_kg' => $programKg[$i],
                'completed_by_machine' => $completedByProgramMachine[$i],
            ];
        }

        return [
            'period' => [
                'start' => $start->copy()->startOfDay(),
                'end' => $end->copy()->endOfDay(),
                'start_parts' => [
                    'day' => (int) $start->day,
                    'month' => (int) $start->month,
                    'year' => (int) $start->year,
                ],
                'end_parts' => [
                    'day' => (int) $end->day,
                    'month' => (int) $end->month,
                    'year' => (int) $end->year,
                ],
            ],
            'detergents' => $detergents,
            'machines' => $machines,
            'programs' => $programs,
            'totals' => [
                'liters_total' => array_sum($liters),
                'kg_total' => array_sum($programKg),
                'machine_kg_total' => array_sum($machineKg),
            ],
        ];
    }

    /**
     * @return array<int, float>
     */
    private function sumStatisticValues(Collection $statistics, string $prefix, int $count): array
    {
        $sums = array_fill(1, $count, 0.0);

        foreach ($statistics as $statistic) {
            $data = is_array($statistic->data) ? $statistic->data : [];

            for ($i = 1; $i <= $count; $i++) {
                $sums[$i] += $this->toFloat($data[$prefix . $i] ?? 0);
            }
        }

        return $sums;
    }

    private function isValidIndex(int $value, int $max): bool
    {
        return $value >= 1 && $value <= $max;
    }

    private function toFloat(mixed $value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
