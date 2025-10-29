<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use App\Models\StationSettingValue;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class StationMachines extends Page
{
    use InteractsWithRecord;
    use EnsuresStationManagementAccess;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-machines';

    protected static ?string $title = 'Стиральные машины';

    public $machines = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->ensureStationManagementAccess();

        $this->loadMachinesFromSettings();
    }

    public function save()
    {
        $this->syncMachineSettings();

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();

        $this->loadMachinesFromSettings();
    }

    protected function loadMachinesFromSettings(): void
    {
        $this->record->load('settingValues');

        $names = [];
        for ($index = 1; $index <= 6; $index++) {
            $block = 310 + $index; // 311-316
            $names[$index] = $this->valueFromSettings($block, 2, '');
        }
        $loadings = $this->collectBlockValues(322);
        $traces = $this->collectBlockValues(323);
        $actives = $this->collectBlockValues(321);

        $machines = [];

        for ($index = 1; $index <= 6; $index++) {
            $machines[] = [
                'name' => (string) ($names[$index] ?? ''),
                'loading' => $this->numericOrEmpty($loadings[$index] ?? ''),
                'trace' => $this->numericOrEmpty($traces[$index] ?? ''),
                'active' => $this->booleanValue($actives[$index] ?? null),
            ];
        }

        $this->machines = $machines;
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

    protected function syncMachineSettings(): void
    {
        foreach ($this->machines as $offset => $machine) {
            $index = $offset + 1;
            $nameBlock = 310 + $index;

            StationSettingValue::updateOrCreate(
                [
                    'station_id' => $this->record->id,
                    'block_number' => $nameBlock,
                    'setting_index' => 3,
                ],
                [
                    'value' => (string) ($machine['name'] ?? ''),
                ],
            );

            StationSettingValue::updateOrCreate(
                [
                    'station_id' => $this->record->id,
                    'block_number' => 322,
                    'setting_index' => $index,
                ],
                [
                    'value' => $this->stringValue($machine['loading'] ?? ''),
                ],
            );

            StationSettingValue::updateOrCreate(
                [
                    'station_id' => $this->record->id,
                    'block_number' => 323,
                    'setting_index' => $index,
                ],
                [
                    'value' => $this->stringValue($machine['trace'] ?? ''),
                ],
            );

            StationSettingValue::updateOrCreate(
                [
                    'station_id' => $this->record->id,
                    'block_number' => 321,
                    'setting_index' => $index,
                ],
                [
                    'value' => $this->booleanValue($machine['active'] ?? false) ? '1' : '0',
                ],
            );
        }

        $this->record->update([
            'machines_data' => $this->machines,
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
