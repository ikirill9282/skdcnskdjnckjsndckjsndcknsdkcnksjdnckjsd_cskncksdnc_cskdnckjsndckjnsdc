<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use App\Models\StationSettingValue;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class StationDetergents extends Page
{
    use InteractsWithRecord;
    use EnsuresStationManagementAccess;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-detergents';

    protected static ?string $title = 'Моющие средства';

    public $detergents = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->ensureStationManagementAccess();

        $this->loadDetergentSettings();
    }

    public function save()
    {
        $this->syncDetergentSettings();

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();

        $this->loadDetergentSettings();
    }

    protected function loadDetergentSettings(): void
    {
        $this->record->load('settingValues');

        $names = [];
        for ($index = 1; $index <= 8; $index++) {
            $block = 330 + $index; // 331-338
            $names[$index] = $this->valueFromSettings($block, 1, '');
        }

        $containers = $this->collectBlockValues(342);
        $densities = $this->collectBlockValues(347);
        $calibrations = $this->collectBlockValues(340);
        $actives = $this->collectBlockValues(341);

        $detergents = [];

        for ($index = 1; $index <= 8; $index++) {
            $detergents[] = [
                'name' => (string) ($names[$index] ?? ''),
                'container' => $this->numericOrEmpty($containers[$index] ?? ''),
                'density' => $this->numericOrEmpty($densities[$index] ?? ''),
                'calibration' => $this->numericOrEmpty($calibrations[$index] ?? ''),
                'active' => $this->booleanValue($actives[$index] ?? null),
            ];
        }

        $this->detergents = $detergents;
    }

    protected function collectBlockValues(int $block): array
    {
        return $this->record->settingValues
            ->where('block_number', $block)
            ->sortBy('setting_index')
            ->mapWithKeys(fn ($item) => [$item->setting_index => $item->value])
            ->toArray();
    }

    protected function valueFromSettings(int $block, int $index, mixed $default = null): mixed
    {
        $value = $this->record->settingValues
            ->first(fn ($item) => (int) $item->block_number === $block && (int) $item->setting_index === $index)
            ?->value;

        return $value ?? $default;
    }

    protected function numericOrEmpty(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return '';
        }

        return is_numeric($value) ? 0 + $value : $value;
    }

    protected function booleanValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return false;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    protected function syncDetergentSettings(): void
    {
        foreach ($this->detergents as $offset => $detergent) {
            $index = $offset + 1;
            $nameBlock = 330 + $index; // 331-338

            StationSettingValue::updateOrCreate(
                [
                    'station_id' => $this->record->id,
                    'block_number' => $nameBlock,
                    'setting_index' => 1,
                ],
                [
                    'value' => (string) ($detergent['name'] ?? ''),
                ],
            );

            StationSettingValue::updateOrCreate(
                [
                    'station_id' => $this->record->id,
                    'block_number' => 342,
                    'setting_index' => $index,
                ],
                [
                    'value' => $this->stringValue($detergent['container'] ?? ''),
                ],
            );

            StationSettingValue::updateOrCreate(
                [
                    'station_id' => $this->record->id,
                    'block_number' => 347,
                    'setting_index' => $index,
                ],
                [
                    'value' => $this->stringValue($detergent['density'] ?? ''),
                ],
            );

            StationSettingValue::updateOrCreate(
                [
                    'station_id' => $this->record->id,
                    'block_number' => 340,
                    'setting_index' => $index,
                ],
                [
                    'value' => $this->stringValue($detergent['calibration'] ?? ''),
                ],
            );

            StationSettingValue::updateOrCreate(
                [
                    'station_id' => $this->record->id,
                    'block_number' => 341,
                    'setting_index' => $index,
                ],
                [
                    'value' => $this->booleanValue($detergent['active'] ?? false) ? '1' : '0',
                ],
            );
        }

        $this->record->update([
            'detergents_data' => $this->detergents,
        ]);
    }

    protected function stringValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
