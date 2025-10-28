<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StationResource\Pages;
use App\Models\Station;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Pages\Page; 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StationResource extends Resource
{
    protected static ?string $model = Station::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    
    protected static ?string $navigationLabel = 'Станции';
    
    protected static ?string $modelLabel = 'Станция';
    
    protected static ?string $pluralModelLabel = 'Станции';

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('company-admin')) {
            return $user->companies()->exists();
        }

        if ($user->hasRole('manager')) {
            return $user->stations()->exists();
        }

        if ($user->hasRole('client')) {
            return $user->stations()->exists();
        }

        return false;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('company-admin')) {
            return $user->companies()->exists();
        }

        if ($user->hasRole('manager')) {
            return $user->stations()->exists();
        }

        if ($user->hasRole('client')) {
            return $user->stations()->exists();
        }

        return false;
    }

    public static function form(Form $form): Form
		{
				return $form
						->schema([
								Forms\Components\TextInput::make('code')
										->required()
										->maxLength(255)
										->label('Номер станции'),
										
								Forms\Components\TextInput::make('name')
										->required()
										->maxLength(255)
										->label('Название станции'),
										
								Forms\Components\TextInput::make('region')
										->maxLength(255)
										->label('Регион'),
										
								Forms\Components\Select::make('company_id')
										->relationship('company', 'name')
										->required()
										->disabled(fn (): bool => auth()->user()?->hasRole('manager') ?? false)
										->label('Компания'),
										
								Forms\Components\Toggle::make('is_active')
										->default(true)
										->label('Активна'),
						]);
		}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Номер станции'),
                    
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Название станции'),

								Tables\Columns\TextColumn::make('region')
										->searchable()
										->sortable()
										->label('Регион'),
                    
                Tables\Columns\TextColumn::make('company.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->label('Компания'),
                    
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Активна'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Создано'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record): bool => static::userHasStationAccess($record) && ! auth()->user()?->hasRole('client')),
                Tables\Actions\Action::make('statistics')
                    ->label('Статистика')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn ($record): string => static::getUrl('statistics', ['record' => $record]))
                    ->visible(fn ($record): bool => static::userHasStationAccess($record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (): bool => auth()->user()?->hasRole('super-admin') ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasRole('super-admin') ?? false),
                ]),
            ]);
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'company-admin']) ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if (! static::userHasStationAccess($record)) {
            return false;
        }

        return ! $user->hasRole('client');
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
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

        if ($user->hasRole('manager')) {
            $stationIds = $user->stations()->pluck('stations.id')->all();

            if (empty($stationIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('id', $stationIds);
        }

        if ($user->hasRole('client')) {
            $stationIds = $user->stations()->pluck('stations.id')->all();

            if (empty($stationIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('id', $stationIds);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function userHasStationAccess(Model $record): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->hasRole('super-admin')) {
            return true;
        }

        if ($user->hasRole('company-admin')) {
            return $user->companies()
                ->where('companies.id', $record->company_id)
                ->exists();
        }

        if ($user->hasAnyRole(['manager', 'client'])) {
            return $user->stations()->whereKey($record->getKey())->exists();
        }

        return false;
    }

    public static function getPages(): array
		{
				return [
						'index' => Pages\ListStations::route('/'),
						'create' => Pages\CreateStation::route('/create'),
						'edit' => Pages\EditStation::route('/{record}/edit'),
						'status' => Pages\StationStatus::route('/{record}/status'),
						'manual' => Pages\StationManual::route('/{record}/manual'),
						'machines' => Pages\StationMachines::route('/{record}/machines'),
						'detergents' => Pages\StationDetergents::route('/{record}/detergents'),
						'auto-programs' => Pages\StationAutoPrograms::route('/{record}/auto-programs'),
						'manual-programs' => Pages\StationManualPrograms::route('/{record}/manual-programs'),
						'statistics' => Pages\StationStatistics::route('/{record}/statistics'),
						'logs' => Pages\StationLogs::route('/{record}/logs'),
						'parameters' => Pages\StationParameters::route('/{record}/parameters'),
				];
		}
		public static function getRecordSubNavigation(Page $page): array
		{
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        if ($user->hasRole('client')) {
            return $page->generateNavigationItems([
                Pages\StationStatistics::class,
            ]);
        }

        return $page->generateNavigationItems([
            Pages\EditStation::class,
            Pages\StationStatus::class,
            Pages\StationManual::class,
            Pages\StationMachines::class,
            Pages\StationDetergents::class,
            Pages\StationAutoPrograms::class,
            Pages\StationManualPrograms::class,
            Pages\StationStatistics::class,
            Pages\StationLogs::class,
            Pages\StationParameters::class,
        ]);
		}


}
