<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class StationMachines extends Page
{
    use InteractsWithRecord;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-machines';

    protected static ?string $title = 'Стиральные машины';

    public $machines = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        // Загружаем данные стиральных машин (можно из JSON-поля или связанной таблицы)
        $this->machines = $this->record->machines_data ?? [
            ['name' => 'Electr 40', 'loading' => 40, 'trace' => 10, 'active' => true],
            ['name' => 'Electr 40', 'loading' => 40, 'trace' => 8, 'active' => true],
            ['name' => 'Electr 40', 'loading' => 40, 'trace' => 6, 'active' => true],
            ['name' => 'Electr 40', 'loading' => 40, 'trace' => 4, 'active' => true],
            ['name' => 'Electr 10', 'loading' => 10, 'trace' => 12, 'active' => true],
            ['name' => 'Electr 10', 'loading' => 10, 'trace' => 14, 'active' => true],
        ];
    }

    public function save()
    {
        // Сохраняем данные машин
        $this->record->update([
            'machines_data' => $this->machines,
        ]);

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();
    }
}
