<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class StationStatus extends Page
{
    use InteractsWithRecord;
    use EnsuresStationManagementAccess;
    use DisplaysStationHeading;

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

    protected function coerceNumeric(mixed $value, float $default = 0): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        return $default;
    }
}
