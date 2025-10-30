<?php

namespace App\Services\StationSettings;

use App\Models\StationSettingValue;

class StationSettingValueWriter
{
    /**
     * Persist a single setting value and report whether it changed.
     */
    public static function write(int $stationId, int $blockNumber, int $settingIndex, mixed $value): bool
    {
        $normalized = self::normalize($value);

        $record = StationSettingValue::firstOrNew([
            'station_id' => $stationId,
            'block_number' => $blockNumber,
            'setting_index' => $settingIndex,
        ]);

        if ($record->exists && $record->value === $normalized) {
            return false;
        }

        $record->value = $normalized;
        $record->save();

        return true;
    }

    protected static function normalize(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        return (string) $value;
    }
}
