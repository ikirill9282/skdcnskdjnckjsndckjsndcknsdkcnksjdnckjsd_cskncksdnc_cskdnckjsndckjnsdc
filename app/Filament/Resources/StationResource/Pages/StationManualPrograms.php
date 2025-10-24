<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use App\Models\Program;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class StationManualPrograms extends Page
{
    use InteractsWithRecord;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-manual-programs';

    protected static ?string $title = 'Программы (ручн)';

    public $programs = [];
    public $selectedProgram = null;
    public $programName = '';
    public $activeMachines = [];
    public $signals = [];
    public $delays = [];
    public $loadPercentage = 100;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        
        // Инициализируем программы если их нет
        $this->initializePrograms();
        
        $this->programs = $this->record->manual_programs_data ?? $this->getDefaultPrograms();
        
        if (!empty($this->programs)) {
            $this->selectedProgram = array_key_first($this->programs);
            $this->loadProgram($this->selectedProgram);
        }
    }

    public function updatedSelectedProgram($value)
    {
        $this->loadProgram($value);
    }

    public function loadProgram($programId)
    {
        if (isset($this->programs[$programId])) {
            $program = $this->programs[$programId];
            
            // Загружаем название из таблицы programs
            $programModel = Program::where('station_id', $this->record->id)
                ->where('program_number', $programId)
                ->first();
                
            $this->programName = $programModel ? $programModel->name : $program['name'];
            $this->activeMachines = $program['active_machines'];
            $this->signals = $program['signals'];
            $this->delays = $program['delays'];
            $this->loadPercentage = $program['load_percentage'];
        }
    }

    public function save()
    {
        if ($this->selectedProgram !== null) {
            $this->programs[$this->selectedProgram] = [
                'name' => $this->programName,
                'active_machines' => $this->activeMachines,
                'signals' => $this->signals,
                'delays' => $this->delays,
                'load_percentage' => $this->loadPercentage,
            ];
        }

        // Сохраняем название в таблицу programs
        Program::updateOrCreate(
            [
                'station_id' => $this->record->id,
                'program_number' => $this->selectedProgram,
            ],
            [
                'name' => $this->programName,
            ]
        );

        $this->record->update([
            'manual_programs_data' => $this->programs,
        ]);

        Notification::make()
            ->title('Программа сохранена')
            ->success()
            ->send();
    }

    private function initializePrograms()
    {
        // Создаём 19 программ если их нет
        for ($i = 1; $i <= 19; $i++) {
            Program::firstOrCreate(
                [
                    'station_id' => $this->record->id,
                    'program_number' => $i,
                ],
                [
                    'name' => "Программа $i",
                ]
            );
        }
    }

    public function getProgramOptions()
    {
        return Program::where('station_id', $this->record->id)
            ->orderBy('program_number')
            ->pluck('name', 'program_number')
            ->toArray();
    }

    private function getDefaultPrograms()
    {
        $defaults = [];
        for ($i = 1; $i <= 19; $i++) {
            $defaults[(string)$i] = [
                'name' => "Программа $i",
                'active_machines' => [false, false, false, false, false, false],
                'signals' => array_fill(0, 8, array_fill(0, 3, null)),
                'delays' => array_fill(0, 8, array_fill(0, 3, null)),
                'load_percentage' => 100,
            ];
        }
        return $defaults;
    }
}
