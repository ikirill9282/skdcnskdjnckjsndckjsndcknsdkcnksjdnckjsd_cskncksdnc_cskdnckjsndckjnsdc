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

class StationResource extends Resource
{
    protected static ?string $model = Station::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    
    protected static ?string $navigationLabel = 'Станции';
    
    protected static ?string $modelLabel = 'Станция';
    
    protected static ?string $pluralModelLabel = 'Станции';

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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(function ($query) {
                $user = auth()->user();
                
                // Для super-admin показываем все станции
                if ($user->hasRole('super-admin')) {
                    return $query;
                }
                
                // Получаем компанию пользователя
                $company = $user->companies()->first();
                
                if (!$company) {
                    return $query->whereRaw('1 = 0'); // Пустой результат
                }
                
                // Фильтруем по компании пользователя
                return $query->where('company_id', $company->id);
            });
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
