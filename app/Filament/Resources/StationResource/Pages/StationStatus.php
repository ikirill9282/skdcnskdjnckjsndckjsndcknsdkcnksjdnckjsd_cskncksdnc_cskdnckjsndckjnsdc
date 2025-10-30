<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use App\Services\StationSettings\SettingBlockChangeTracker;
use App\Services\StationSettings\StationSettingValueWriter;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class StationStatus extends Page
{
    use InteractsWithRecord;
    use EnsuresStationManagementAccess;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-status';

    protected static ?string $title = 'Текущее состояние';

    public $status;
    public $detergent;
    public $volume;
    public $washing_machine;
    public $process_completion;
    protected array $settingsBlock = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->ensureStationManagementAccess();
        $this->loadSettingsBlock();

        $this->status = $this->valueWithFallback(
            $this->record->current_status,
            $this->blockValue(1),
        );

        $this->detergent = $this->valueWithFallback(
            $this->record->current_detergent,
            $this->blockValue(2),
        );

        $this->volume = $this->coerceNumeric(
            $this->valueWithFallback(
                $this->record->current_volume,
                $this->blockValue(3),
                0,
            ),
        );

        $this->washing_machine = $this->valueWithFallback(
            $this->record->current_washing_machine,
            $this->blockValue(4),
        );

        $this->process_completion = $this->coerceNumeric(
            $this->valueWithFallback(
                $this->record->current_process_completion,
                $this->blockValue(5),
                0,
            ),
        );
    }

    public function save()
    {
        $this->validate([
            'status' => 'required|string|max:255',
            'detergent' => 'required|string|max:255',
            'volume' => 'required|numeric|min:0',
            'washing_machine' => 'required|string|max:255',
            'process_completion' => 'required|numeric|min:0|max:100',
        ]);

        // Сохраняем данные в модель Station
        $this->record->update([
            'current_status' => $this->status,
            'current_detergent' => $this->detergent,
            'current_volume' => $this->volume,
            'current_washing_machine' => $this->washing_machine,
            'current_process_completion' => $this->process_completion,
        ]);

        $this->syncSettingsBlock();

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();
    }

    protected function loadSettingsBlock(): void
    {
        $this->record->loadMissing('settingValues');

        $this->settingsBlock = $this->record->settingValues
            ->where('block_number', 306)
            ->sortBy('setting_index')
            ->mapWithKeys(fn ($item) => [$item->setting_index => $item->value])
            ->toArray();
    }

    protected function blockValue(int $index): mixed
    {
        return $this->settingsBlock[$index] ?? null;
    }

    protected function valueWithFallback(mixed $primary, mixed $fallback, mixed $default = ''): mixed
    {
        if ($fallback !== null && $fallback !== '') {
            return $fallback;
        }

        if ($primary !== null && $primary !== '') {
            return $primary;
        }

        return $default;
    }

    protected function syncSettingsBlock(): void
    {
        $stationId = $this->record->id;
        $changedBlocks = [];

        $map = [
            1 => $this->status,
            2 => $this->detergent,
            3 => $this->volume,
            4 => $this->washing_machine,
            5 => $this->process_completion,
        ];

        foreach ($map as $index => $value) {
            if (StationSettingValueWriter::write(
                $stationId,
                306,
                $index,
                (string) $value,
            )) {
                $changedBlocks[306] = true;
            }
        }

        $blockNumbers = $changedBlocks !== []
            ? array_keys($changedBlocks)
            : [306];

        SettingBlockChangeTracker::markBlocksChanged(
            $stationId,
            $blockNumbers,
            'status'
        );
    }

    protected function coerceNumeric(mixed $value, float $default = 0): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return $default;
    }
}
