<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use App\Models\StationLog;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;

class StationLogs extends Page
{
    use InteractsWithRecord;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-logs';

    protected static ?string $title = 'Журнал работ';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getLogs()
    {
        return $this->record->logs()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }
}
