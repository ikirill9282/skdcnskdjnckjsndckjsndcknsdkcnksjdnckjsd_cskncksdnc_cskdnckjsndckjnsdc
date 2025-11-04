<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStation extends EditRecord
{
    use DisplaysStationHeading;

    protected static string $resource = StationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->hasRole('super-admin') ?? false),
        ];
    }
}
