<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use App\Models\Program;
use App\Services\StationSettings\SettingBlockChangeTracker;
use App\Services\StationSettings\StationSettingValueWriter;
use Filament\Resources\Pages\Page;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Notifications\Notification;

class StationManualPrograms extends Page
{
    use InteractsWithRecord;
    use EnsuresStationManagementAccess;
    use DisplaysStationHeading;

    protected static string $resource = StationResource::class;

    protected static string $view = 'filament.resources.station-resource.pages.station-manual-programs';

    protected static ?string $title = 'Программы (ручн)';

    public array $programs = [];
    public ?int $selectedProgram = null;
    public string $programName = '';
    public array $activeMachines = [];
    public array $signals = [];
    public array $delays = [];
    public $loadPercentage = 0;
    public array $programOptions = [];
    protected array $programNameCache = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->ensureStationManagementAccess();
        $this->initializePrograms();

        $this->record->load('settingValues');

        $this->programs = $this->buildProgramsFromSettings();
        $this->refreshProgramOptions();

        $this->selectedProgram = array_key_first($this->programOptions) ?? 1;
        $this->loadProgram($this->selectedProgram);
    }

    public function updatedSelectedProgram($value): void
    {
        $this->loadProgram((int) $value);
    }

    public function loadProgram(int $programId): void
    {
        $data = $this->getProgramData($programId);

        $this->selectedProgram = $programId;
        $this->programs[$programId] = $data;
        $this->programName = $data['name'];
        $this->activeMachines = $data['active_machines'];
        $this->signals = $data['signals'];
        $this->delays = $data['delays'];
        $this->loadPercentage = $data['load_percentage'];
    }

    public function save()
    {
        $programNumber = $this->selectedProgram ?? 1;

        $this->syncProgramSettings($programNumber);

        Program::updateOrCreate(
            [
                'station_id' => $this->record->id,
                'program_number' => $programNumber,
            ],
            [
                'name' => $this->programName,
            ]
        );

        $this->programNameCache = [];

        $this->record->load('settingValues');
        $this->programs = $this->buildProgramsFromSettings();
        $this->refreshProgramOptions();
        $this->loadProgram($programNumber);

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

    public function getProgramOptions(): array
    {
        return $this->programOptions;
    }

    private function buildProgramsFromSettings(): array
    {
        $programs = [];

        for ($i = 1; $i <= 19; $i++) {
            $programs[$i] = $this->getProgramData($i);
        }

        return $programs;
    }

    private function getProgramData(int $number): array
    {
        $defaultName = "Программа {$number}";
        $fallbackName = $this->fallbackProgramName($number, $defaultName);
        $name = (string) $this->getSettingValue($number * 10 + 109, 1, $fallbackName);

        $active = [];
        $activeBlock = $number * 10 + 105;
        for ($i = 1; $i <= 6; $i++) {
            $value = $this->getSettingValue($activeBlock, $i, '0');
            $active[] = $this->booleanValue($value);
        }

        $signals = [];
        for ($row = 1; $row <= 8; $row++) {
            $rowData = [];
            for ($col = 1; $col <= 3; $col++) {
                $block = $number * 10 + 100 + $col;
                $value = $this->getSettingValue($block, $row, '');
                $rowData[] = $this->numericOrEmpty($value);
            }
            $signals[] = $rowData;
        }

        $delays = [];
        for ($row = 1; $row <= 8; $row++) {
            $rowData = [];
            for ($col = 1; $col <= 3; $col++) {
                $block = $number * 10 + 105 + $col;
                $value = $this->getSettingValue($block, $row, '');
                $rowData[] = $this->numericOrEmpty($value);
            }
            $delays[] = $rowData;
        }

        $loadValue = $this->getSettingValue($activeBlock, 9, '');
        $loadPercentage = is_numeric($loadValue) ? 0 + $loadValue : 0;

        return [
            'name' => $name !== '' ? $name : $fallbackName,
            'active_machines' => $active,
            'signals' => $signals,
            'delays' => $delays,
            'load_percentage' => $loadPercentage,
        ];
    }

    private function fallbackProgramName(int $number, string $default): string
    {
        if ($this->programNameCache === []) {
            $this->programNameCache = Program::where('station_id', $this->record->id)
                ->pluck('name', 'program_number')
                ->toArray();
        }

        return $this->programNameCache[$number] ?? $default;
    }

    private function refreshProgramOptions(): void
    {
        $options = [];

        for ($i = 1; $i <= 19; $i++) {
            $options[$i] = $this->programs[$i]['name'] ?? "Программа {$i}";
        }

        $this->programOptions = $options;
    }

    private function syncProgramSettings(int $programNumber): void
    {
        $stationId = $this->record->id;
        $changedBlocks = [];

        $activeBlock = $programNumber * 10 + 105;

        for ($offset = 0; $offset < 6; $offset++) {
            $active = $this->activeMachines[$offset] ?? false;

            if (StationSettingValueWriter::write(
                $stationId,
                $activeBlock,
                $offset + 1,
                $this->booleanValue($active) ? '1' : '0',
            )) {
                $changedBlocks[$activeBlock] = true;
            }
        }

        if (StationSettingValueWriter::write(
            $stationId,
            $activeBlock,
            9,
            $this->stringValue($this->loadPercentage),
        )) {
            $changedBlocks[$activeBlock] = true;
        }

        $nameBlock = $programNumber * 10 + 109;

        if (StationSettingValueWriter::write(
            $stationId,
            $nameBlock,
            1,
            (string) $this->programName,
        )) {
            $changedBlocks[$nameBlock] = true;
        }

        for ($col = 1; $col <= 3; $col++) {
            $signalBlock = $programNumber * 10 + 100 + $col;
            $delayBlock = $programNumber * 10 + 105 + $col;

            for ($row = 1; $row <= 8; $row++) {
                $signalValue = $this->signals[$row - 1][$col - 1] ?? '';
                $delayValue = $this->delays[$row - 1][$col - 1] ?? '';

                if (StationSettingValueWriter::write(
                    $stationId,
                    $signalBlock,
                    $row,
                    $this->stringValue($signalValue),
                )) {
                    $changedBlocks[$signalBlock] = true;
                }

                if (StationSettingValueWriter::write(
                    $stationId,
                    $delayBlock,
                    $row,
                    $this->stringValue($delayValue),
                )) {
                    $changedBlocks[$delayBlock] = true;
                }
            }
        }

        if ($changedBlocks !== []) {
            SettingBlockChangeTracker::markBlocksChanged(
                $stationId,
                array_keys($changedBlocks),
                'manual-programs'
            );
        }
    }

    private function getSettingValue(int $block, int $index, mixed $default = null): mixed
    {
        $value = $this->record->settingValues
            ->first(fn ($item) => (int) $item->block_number === $block && (int) $item->setting_index === $index)
            ?->value;

        return $value ?? $default;
    }

    private function numericOrEmpty(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return '';
        }

        return is_numeric($value) ? 0 + $value : $value;
    }

    private function booleanValue(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return false;
        }

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    private function stringValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return (string) $value;
    }
}
