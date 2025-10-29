<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class StationManual extends Page
{
    use InteractsWithRecord;
    use EnsuresStationManagementAccess;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-manual';

    protected static ?string $title = 'Ручная подача';

    public $detergent;
    public $ml;
    public $washing_machine;
    public $process_completion;

    protected array $manualSettings = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->ensureStationManagementAccess();

        $this->loadManualSettings();

        $this->detergent = (string) $this->manualValue(1, '');
        $mlValue = $this->manualValue(2, '');
        $this->ml = is_numeric($mlValue) ? 0 + $mlValue : $mlValue;
        $this->washing_machine = (string) $this->manualValue(3, '');
        $processValue = $this->manualValueFromBlock(306, 5, '');
        $this->process_completion = is_numeric($processValue) ? 0 + $processValue : $processValue;
    }

    public function submit()
    {
        $this->validate([
            'detergent' => 'required|string|max:255',
            'ml' => 'required|numeric|min:0',
            'washing_machine' => 'required|string|max:255',
        ]);

        // Здесь можно добавить логику отправки команды на станцию
        // Или сохранение в лог ручных подач
        
        Notification::make()
            ->title('Подача выполнена')
            ->body("Средство: {$this->detergent}, Объем: {$this->ml} мл, Машина: {$this->washing_machine}")
            ->success()
            ->send();

        // Очищаем форму после успешной подачи
        $this->reset(['detergent', 'ml', 'washing_machine']);
    }

    protected function loadManualSettings(): void
    {
        $this->record->loadMissing('settingValues');

        $this->manualSettings = $this->record->settingValues
            ->where('block_number', 305)
            ->sortBy('setting_index')
            ->mapWithKeys(fn ($item) => [$item->setting_index => $item->value])
            ->toArray();
    }

    protected function manualValue(int $index, mixed $default = null): mixed
    {
        return $this->manualSettings[$index] ?? $default;
    }

    protected function manualValueFromBlock(int $block, int $index, mixed $default = null): mixed
    {
        $value = $this->record->settingValues
            ->first(fn ($item) => (int) $item->block_number === $block && (int) $item->setting_index === $index)
            ?->value;

        return $value ?? $default;
    }
}
