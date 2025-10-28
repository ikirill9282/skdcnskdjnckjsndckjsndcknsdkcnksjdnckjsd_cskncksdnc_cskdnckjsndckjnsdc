<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
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

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->ensureStationManagementAccess();
        
        // Загружаем текущие данные (можно из связанной модели или JSON-поля)
        $this->status = $this->record->current_status ?? '';
        $this->detergent = $this->record->current_detergent ?? '';
        $this->volume = $this->record->current_volume ?? '';
        $this->washing_machine = $this->record->current_washing_machine ?? '';
        $this->process_completion = $this->record->current_process_completion ?? 0;
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

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();
    }
}
