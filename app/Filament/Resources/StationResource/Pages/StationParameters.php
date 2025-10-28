<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class StationParameters extends Page
{
    use InteractsWithRecord;
    use EnsuresStationManagementAccess;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-parameters';

    protected static ?string $title = 'Параметры';

    public $activation_date;
    public $days_worked;
    public $service_date;
    public $warnings;
    public $errors;
    public $station_name;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->ensureStationManagementAccess();
        
        // Загружаем данные
        $this->activation_date = $this->record->activation_date ? $this->record->activation_date->format('Y-m-d') : '';
        $this->days_worked = $this->record->days_worked ?? 0;
        $this->service_date = $this->record->service_date ? $this->record->service_date->format('Y-m-d') : '';
        $this->warnings = $this->record->warnings ?? '';
        $this->errors = $this->record->errors ?? '';
        $this->station_name = $this->record->name ?? '';
    }

    public function save()
    {
        $this->validate([
            'station_name' => 'required|string|max:255',
            'days_worked' => 'required|integer|min:0',
            'activation_date' => 'nullable|date',
            'service_date' => 'nullable|date',
            'warnings' => 'nullable|string',
            'errors' => 'nullable|string',
        ]);

        // Сохраняем данные
        $this->record->update([
            'name' => $this->station_name,
            'activation_date' => $this->activation_date,
            'days_worked' => $this->days_worked,
            'service_date' => $this->service_date,
            'warnings' => $this->warnings,
            'errors' => $this->errors,
        ]);

        Notification::make()
            ->title('Параметры сохранены')
            ->success()
            ->send();
    }
}
