<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use App\Models\Statistic;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class StationStatistics extends Page
{
    use InteractsWithRecord;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-statistics';

    protected static ?string $title = 'Статистика';

    public $startDate;
    public $endDate;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
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
		public function getProgramNames()
		{
				// Загружаем 19 программ из базы данных для текущей станции
				$programs = [];
				
				for ($i = 1; $i <= 19; $i++) {
						$program = \App\Models\Program::where('station_id', $this->record->id)
								->where('program_number', $i)
								->first();
						
						$programs[] = $program ? $program->name : "Программа $i";
				}
				
				return $programs;
		}

		public function getChemNames()
		{
				// Загружаем названия моющих средств из JSON-поля detergents_data
				$detergents = $this->record->detergents_data ?? [];
				
				$names = [];
				for ($i = 0; $i < 8; $i++) {
						if (isset($detergents[$i]['name'])) {
								$names[] = $detergents[$i]['name'];
						} else {
								$names[] = "Средство " . ($i + 1);
						}
				}
				
				return $names;
		}
		public function getMachineNames()
		{
				// Загружаем названия стиральных машин из JSON-поля machines_data
				$machines = $this->record->machines_data ?? [];
				
				$names = [];
				foreach ($machines as $machine) {
						if (isset($machine['name'])) {
								$names[] = $machine['name'];
						}
				}
				
				// Если нет данных, заполняем дефолтными значениями
				if (empty($names)) {
						for ($i = 1; $i <= 8; $i++) {
								$names[] = "Машина $i";
						}
				}
				
				return $names;
		}



}
