<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\StationResource;
use App\Models\Station;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class UserStationsWidget extends TableWidget
{
    protected static ?string $heading = 'Мои станции';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->label('Номер')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Название')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('region')
                    ->label('Регион')
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Компания')
                    ->badge()
                    ->toggleable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Активна')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('Открыть')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Station $record): string => $this->resolveStationUrl($record))
                    ->visible(fn (Station $record): bool => StationResource::userHasStationAccess($record)),
            ])
            ->paginated(10)
            ->defaultSort('name')
            ->emptyStateHeading('Нет доступных станций')
            ->emptyStateDescription('Попросите администратора назначить вам станции, чтобы они появились в списке.');
    }

    protected function getTableQuery(): Builder
    {
        $query = Station::query()
            ->with('company')
            ->orderBy('name');

        $user = auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->hasRole('super-admin')) {
            return $query;
        }

        if ($user->hasRole('company-admin')) {
            $companyIds = $user->companies()->pluck('companies.id')->all();

            if (empty($companyIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('company_id', $companyIds);
        }

        if ($user->hasAnyRole(['manager', 'client'])) {
            $stationIds = $user->stations()->pluck('stations.id')->all();

            if (empty($stationIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('id', $stationIds);
        }

        return $query->whereRaw('1 = 0');
    }

    protected function resolveStationUrl(Station $station): string
    {
        $user = auth()->user();

        if (! $user) {
            return '#';
        }

        if ($user->hasRole('client')) {
            return StationResource::getUrl('statistics', ['record' => $station]);
        }

        if ($user->hasRole('manager')) {
            return StationResource::getUrl('status', ['record' => $station]);
        }

        return StationResource::getUrl('edit', ['record' => $station]);
    }
}
