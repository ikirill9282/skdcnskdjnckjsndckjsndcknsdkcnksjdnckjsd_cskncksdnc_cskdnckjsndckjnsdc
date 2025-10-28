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

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->ensureStationManagementAccess();
        
        // Инициализация пустых значений
        $this->detergent = '';
        $this->ml = '';
        $this->washing_machine = '';
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
}
