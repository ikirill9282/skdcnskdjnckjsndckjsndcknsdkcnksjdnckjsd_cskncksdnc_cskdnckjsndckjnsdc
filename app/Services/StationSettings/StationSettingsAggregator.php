<?php

namespace App\Services\StationSettings;

use App\Models\Program;
use App\Models\Station;
use App\Models\StationSettingValue;
use Illuminate\Support\Collection;

class StationSettingsAggregator
{
    /**
     * Rebuild station aggregate columns (machines, detergents, programs, status, etc.)
     * using the latest station setting values.
     */
    public static function sync(Station $station): void
    {
        $values = StationSettingValue::query()
            ->where('station_id', $station->id)
            ->get()
            ->groupBy('block_number')
            ->map(fn (Collection $items) => $items->keyBy('setting_index'));

        $programNames = Program::query()
            ->where('station_id', $station->id)
            ->pluck('name', 'program_number')
            ->all();

        $updates = [];

        $machines = self::buildMachinesData($values);
        if ($machines !== $station->machines_data) {
            $updates['machines_data'] = $machines;
        }

        $detergents = self::buildDetergentsData($values);
        if ($detergents !== $station->detergents_data) {
            $updates['detergents_data'] = $detergents;
        }

        $statusUpdates = self::buildStatusFields($values);
        foreach ($statusUpdates as $field => $value) {
            if ($station->{$field} !== $value) {
                $updates[$field] = $value;
            }
        }

        $autoPrograms = self::buildAutoProgramsData($values, $programNames);
        if ($autoPrograms !== $station->auto_programs_data) {
            $updates['auto_programs_data'] = $autoPrograms;
        }

        $manualPrograms = self::buildManualProgramsData($values, $programNames);
        if ($manualPrograms !== $station->manual_programs_data) {
            $updates['manual_programs_data'] = $manualPrograms;
        }

        if ($updates !== []) {
            $station->forceFill($updates)->save();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function buildMachinesData(Collection $values): array
    {
        $machines = [];

        for ($index = 1; $index <= 6; $index++) {
            $nameBlock = 310 + $index;

            $machines[] = [
                'name' => (string) self::value($values, $nameBlock, 1, ''),
                'loading' => self::numericOrEmpty(self::value($values, 322, $index, '')),
                'trace' => self::numericOrEmpty(self::value($values, 323, $index, '')),
                'active' => self::booleanValue(self::value($values, 321, $index, '0')),
            ];
        }

        return $machines;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected static function buildDetergentsData(Collection $values): array
    {
        $detergents = [];

        for ($index = 1; $index <= 8; $index++) {
            $nameBlock = 330 + $index;

            $detergents[] = [
                'name' => (string) self::value($values, $nameBlock, 1, ''),
                'container' => self::numericOrEmpty(self::value($values, 342, $index, '')),
                'density' => self::numericOrEmpty(self::value($values, 347, $index, '')),
                'calibration' => self::numericOrEmpty(self::value($values, 340, $index, '')),
                'active' => self::booleanValue(self::value($values, 341, $index, '0')),
            ];
        }

        return $detergents;
    }

    /**
     * @return array<string, mixed>
     */
    protected static function buildStatusFields(Collection $values): array
    {
        $fields = [];

        $status = self::value($values, 306, 1);
        if ($status !== null) {
            $fields['current_status'] = (string) $status;
        }

        $detergent = self::value($values, 306, 2);
        if ($detergent !== null) {
            $fields['current_detergent'] = (string) $detergent;
        }

        $volume = self::value($values, 306, 3);
        if ($volume !== null) {
            $fields['current_volume'] = is_numeric($volume) ? (float) $volume : 0.0;
        }

        $machine = self::value($values, 306, 4);
        if ($machine !== null) {
            $fields['current_washing_machine'] = (string) $machine;
        }

        $completion = self::value($values, 306, 5);
        if ($completion !== null) {
            $fields['current_process_completion'] = is_numeric($completion) ? (float) $completion : 0.0;
        }

        return $fields;
    }

    /**
     * @param  array<int, string>  $programNames
     * @return array<int, array<string, mixed>>
     */
    protected static function buildAutoProgramsData(Collection $values, array $programNames): array
    {
        $programs = [];

        for ($number = 1; $number <= 19; $number++) {
            $defaultName = "Программа {$number}";
            $fallbackName = $programNames[$number] ?? $defaultName;
            $nameBlock = $number * 10 + 109;
            $activeBlock = $number * 10 + 104;

            $name = (string) self::value($values, $nameBlock, 1, $fallbackName);
            if ($name === '') {
                $name = $fallbackName;
            }

            $activeMachines = [];
            for ($i = 1; $i <= 6; $i++) {
                $activeMachines[] = self::booleanValue(self::value($values, $activeBlock, $i, '0'));
            }

            $loadValue = self::value($values, $activeBlock, 9, '');
            $loadPercentage = is_numeric($loadValue) ? (float) $loadValue : 0.0;

            $base = self::autoProgramBase($number);
            $scenarios = [];
            for ($row = 1; $row <= 8; $row++) {
                $rowData = [];
                for ($col = 1; $col <= 5; $col++) {
                    $block = $base + $col;
                    $rowData[] = self::numericOrEmpty(self::value($values, $block, $row, ''));
                }
                $scenarios[] = $rowData;
            }

            $programs[$number] = [
                'name' => $name,
                'active_machines' => $activeMachines,
                'scenarios' => $scenarios,
                'load_percentage' => $loadPercentage,
            ];
        }

        return $programs;
    }

    /**
     * @param  array<int, string>  $programNames
     * @return array<int, array<string, mixed>>
     */
    protected static function buildManualProgramsData(Collection $values, array $programNames): array
    {
        $programs = [];

        for ($number = 1; $number <= 19; $number++) {
            $defaultName = "Программа {$number}";
            $fallbackName = $programNames[$number] ?? $defaultName;
            $nameBlock = $number * 10 + 109;
            $activeBlock = $number * 10 + 105;

            $name = (string) self::value($values, $nameBlock, 1, $fallbackName);
            if ($name === '') {
                $name = $fallbackName;
            }

            $activeMachines = [];
            for ($i = 1; $i <= 6; $i++) {
                $activeMachines[] = self::booleanValue(self::value($values, $activeBlock, $i, '0'));
            }

            $loadValue = self::value($values, $activeBlock, 9, '');
            $loadPercentage = is_numeric($loadValue) ? (float) $loadValue : 0.0;

            $signals = [];
            for ($row = 1; $row <= 8; $row++) {
                $rowData = [];
                for ($col = 1; $col <= 3; $col++) {
                    $block = $number * 10 + 100 + $col;
                    $rowData[] = self::numericOrEmpty(self::value($values, $block, $row, ''));
                }
                $signals[] = $rowData;
            }

            $delays = [];
            for ($row = 1; $row <= 8; $row++) {
                $rowData = [];
                for ($col = 1; $col <= 3; $col++) {
                    $block = $number * 10 + 105 + $col;
                    $rowData[] = self::numericOrEmpty(self::value($values, $block, $row, ''));
                }
                $delays[] = $rowData;
            }

            $programs[$number] = [
                'name' => $name,
                'active_machines' => $activeMachines,
                'signals' => $signals,
                'delays' => $delays,
                'load_percentage' => $loadPercentage,
            ];
        }

        return $programs;
    }

    protected static function value(Collection $values, int $block, int $index, mixed $default = null): mixed
    {
        $group = $values->get($block);

        if ($group === null) {
            return $default;
        }

        $item = $group->get($index);

        return $item->value ?? $default;
    }

    protected static function numericOrEmpty(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return '';
        }

        return is_numeric($value) ? 0 + $value : $value;
    }

    protected static function booleanValue(mixed $value): bool
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        if (is_bool($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return false;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    protected static function autoProgramBase(int $programNumber): int
    {
        $offset = $programNumber > 10 ? 95 : 0;

        return $programNumber * 10 - $offset;
    }
}

