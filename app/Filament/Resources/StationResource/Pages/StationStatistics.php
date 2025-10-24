<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use App\Models\StationStatistic;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class StationStatistics extends Page
{
    use InteractsWithRecord;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-statistics';
    
    protected static ?string $title = 'Статистика';

    public $dateFrom;
    public $dateTo;
    public $statistics = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->dateFrom = now()->subMonth()->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
        $this->loadStatistics();
    }
    
    public function updated($property)
    {
        if (in_array($property, ['dateFrom', 'dateTo'])) {
            $this->loadStatistics();
        }
    }
    
    public function loadStatistics()
    {
        $this->statistics = StationStatistic::where('station_id', $this->record->id)
            ->whereBetween('date', [$this->dateFrom, $this->dateTo])
            ->orderBy('date', 'desc')
            ->get();
    }
    
    public function getHeading(): string
    {
        return 'Статистика - ' . $this->record->name;
    }
}
