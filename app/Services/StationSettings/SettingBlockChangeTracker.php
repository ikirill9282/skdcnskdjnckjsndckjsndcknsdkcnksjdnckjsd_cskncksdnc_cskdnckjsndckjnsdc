<?php

namespace App\Services\StationSettings;

use App\Models\Station;
use App\Models\StationSettingBlockUpdate;
use Illuminate\Support\Carbon;

class SettingBlockChangeTracker
{
    public static function markBlocksChanged(int $stationId, array $blockNumbers, ?string $changedBy = null): void
    {
        $uniqueBlocks = array_unique($blockNumbers);

        foreach ($uniqueBlocks as $blockNumber) {
            StationSettingBlockUpdate::updateOrCreate(
                [
                    'station_id' => $stationId,
                    'block_number' => $blockNumber,
                ],
                [
                    'changed_by' => $changedBy,
                    'sent_at' => null,
                ],
            );
        }
    }

    public static function markBlocksSent(int $stationId, array $blockNumbers): void
    {
        if ($blockNumbers === []) {
            return;
        }

        StationSettingBlockUpdate::where('station_id', $stationId)
            ->whereIn('block_number', array_unique($blockNumbers))
            ->update([
                'sent_at' => Carbon::now(),
            ]);
    }

    public static function pendingBlocksForStation(Station $station): array
    {
        return $station->settingBlockUpdates()
            ->whereNull('sent_at')
            ->pluck('block_number')
            ->unique()
            ->values()
            ->all();
    }
}
