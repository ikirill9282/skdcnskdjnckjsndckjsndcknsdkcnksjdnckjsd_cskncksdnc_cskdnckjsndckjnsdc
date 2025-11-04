<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use App\Models\Statistic;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StationStatistics extends Page
{
    use InteractsWithRecord;
    use DisplaysStationHeading;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-statistics';

    protected static ?string $title = 'Статистика';

    public $startDate;
    public $endDate;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->ensureAuthorized();

        // По умолчанию текущий месяц
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function getStatistics()
    {
        return Statistic::where('station_id', $this->record->id)
            ->whereBetween('date', [$this->startDate, $this->endDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    public function deleteStatistic($id)
    {
        abort_unless($this->canManageStatistics(), 403);

        $statistic = Statistic::find($id);

        if ($statistic && $statistic->station_id === $this->record->id) {
            $statistic->delete();

            Notification::make()
                ->title('Запись удалена')
                ->success()
                ->send();
        }
    }

    public function getColumns()
    {
        return [
            'дата',
            'Macro belt - 1',
            'Pastel belt - 2',
            'Pododeyal - 3',
            'Reziar belt - 4',
            'Nаrodach - 5',
            'Strike 40 - 6',
            'Pereari - 7',
            'Strike 30 - 8',
            'Kumyo 90 - 9',
            'Kumyo 60 - 10',
            'Delicate - 11',
            'Forma - 12',
            '-13',
            '-14',
            '-15',
            '-16',
            '-17',
            '-18',
            '-19',
            'всего',
        ];
    }
    public function getProgramNames(): array
    {
        $programs = [];

        for ($i = 1; $i <= 19; $i++) {
            $program = \App\Models\Program::where('station_id', $this->record->id)
                ->where('program_number', $i)
                ->first();

            $programs[] = $program ? $program->name : "Программа $i";
        }

        return $programs;
    }

    public function getChemNames(): array
    {
        $detergents = $this->record->detergents_data ?? [];

        $names = [];
        for ($i = 0; $i < 8; $i++) {
            if (isset($detergents[$i]['name'])) {
                $names[] = $detergents[$i]['name'];
            } else {
                $names[] = 'Средство ' . ($i + 1);
            }
        }

        return $names;
    }

    public function getMachineNames(): array
    {
        $machines = $this->record->machines_data ?? [];

        $names = [];
        foreach ($machines as $machine) {
            if (isset($machine['name'])) {
                $names[] = $machine['name'];
            }
        }

        if (empty($names)) {
            for ($i = 1; $i <= 6; $i++) {
                $names[] = "Машина $i";
            }
        }

        return $names;
    }

    public function canManageStatistics(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (! StationResource::userHasStationAccess($this->record)) {
            return false;
        }

        return $user->hasAnyRole(['super-admin', 'company-admin', 'manager']);
    }

    public function getStationNumber(): string
    {
        return (string) ($this->record->code ?? $this->record->id);
    }

    public function getStationName(): string
    {
        return (string) ($this->record->name ?? 'Без названия');
    }

    public function getStationLogoUrl(): ?string
    {
        $logoPath = $this->record->company?->logo ?? null;

        if (blank($logoPath)) {
            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($logoPath)) {
            return null;
        }

        return $disk->url($logoPath);
    }

    protected function ensureAuthorized(): void
    {
        abort_unless(StationResource::userHasStationAccess($this->record), 403);
    }
}
