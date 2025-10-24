<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class StationDetergents extends Page
{
    use InteractsWithRecord;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-detergents';

    protected static ?string $title = 'Моющие средства';

    public $detergents = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        // Загружаем данные моющих средств
        $this->detergents = $this->record->detergents_data ?? [
            ['name' => 'Power', 'container' => 20, 'rollback' => 20, 'calibration' => 100, 'active' => true],
            ['name' => 'Booster', 'container' => 20, 'rollback' => 0, 'calibration' => 105, 'active' => true],
            ['name' => 'Oxy', 'container' => 20, 'rollback' => 0, 'calibration' => 100, 'active' => true],
            ['name' => 'Soft', 'container' => 20, 'rollback' => 0, 'calibration' => 100, 'active' => true],
            ['name' => '0', 'container' => 20, 'rollback' => 0, 'calibration' => 100, 'active' => false],
            ['name' => '0', 'container' => 20, 'rollback' => 15, 'calibration' => 100, 'active' => false],
            ['name' => '0', 'container' => 20, 'rollback' => 0, 'calibration' => 100, 'active' => false],
            ['name' => '0', 'container' => 20, 'rollback' => 0, 'calibration' => 100, 'active' => false],
        ];
    }

    public function save()
    {
        // Сохраняем данные средств
        $this->record->update([
            'detergents_data' => $this->detergents,
        ]);

        Notification::make()
            ->title('Сохранено')
            ->success()
            ->send();
    }
}
