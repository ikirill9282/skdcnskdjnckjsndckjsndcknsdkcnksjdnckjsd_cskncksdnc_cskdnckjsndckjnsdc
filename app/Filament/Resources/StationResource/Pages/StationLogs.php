<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use App\Models\StationLog;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class StationLogs extends Page
{
    use InteractsWithRecord;
    use EnsuresStationManagementAccess;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-logs';

    protected static ?string $title = 'Журнал работ';

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->ensureStationManagementAccess();
    }

    public function getLogs()
    {
        return $this->record->logs()
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    public function deleteLog($id)
    {
        $this->ensureStationManagementAccess();

        $log = StationLog::find($id);
        
        if ($log && $log->station_id === $this->record->id) {
            $log->delete();
            
            Notification::make()
                ->title('Запись удалена')
                ->success()
                ->send();
        }
    }
}
