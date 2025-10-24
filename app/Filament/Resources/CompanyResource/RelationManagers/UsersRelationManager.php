<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Пользователи';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->label('Имя'),
                    
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->label('Почта'),
                    
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $context): bool => $context === 'create')
                    ->maxLength(255)
                    ->label('Пароль'),
                    
                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Админ',
                        'user' => 'Пользователь',
                    ])
                    ->required()
                    ->default('user')
                    ->label('Роль'),
                    
                Forms\Components\Select::make('station_ids')
                    ->relationship('stations', 'name', function ($query) {
                        $companyId = $this->getOwnerRecord()->id;
                        return $query->where('company_id', $companyId);
                    })
                    ->multiple()
                    ->preload()
                    ->helperText('Если не выбрано ни одной станции, пользователь получит доступ ко всем станциям компании')
                    ->label('Доступ к станциям'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Имя'),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('Почта'),
                    
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->label('Роль системы'),
                    
                Tables\Columns\TextColumn::make('stations.name')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        $companyId = $this->getOwnerRecord()->id;
                        $userStations = $record->stations()
                            ->where('company_id', $companyId)
                            ->get();
                        
                        if ($userStations->isEmpty()) {
                            return ['Все станции'];
                        }
                        
                        return $userStations->pluck('name')->toArray();
                    })
                    ->label('Доступ к станциям'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Создан'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
								Tables\Actions\CreateAction::make()
										->after(function ($record, $livewire) {
												\Log::info('After create - User ID:', ['id' => $record->id]);
												
												// Получаем данные формы
												$formData = $livewire->getMountedTableActionForm()->getRawState();
												\Log::info('Form raw state:', $formData);
												
												// Назначаем роль
												if (isset($formData['role'])) {
														$record->syncRoles([$formData['role']]);
												}
												
												// Привязываем станции
												if (isset($formData['station_ids']) && !empty($formData['station_ids'])) {
														$record->stations()->sync($formData['station_ids']);
												}
										}),

						])



            ->actions([
                Tables\Actions\EditAction::make()
										->fillForm(function ($record): array {
												$role = $record->roles->first();
												$stationIds = $record->stations()->pluck('stations.id')->toArray();
												
												return [
														'name' => $record->name,
														'email' => $record->email,
														'role' => $role ? $role->name : 'user',
														'station_ids' => $stationIds,
												];
										})
										->after(function ($record, $livewire) {
												\Log::info('Edit after - User ID:', ['id' => $record->id]);
												
												// Получаем данные формы
												$formData = $livewire->getMountedTableActionForm()->getRawState();
												\Log::info('Edit form raw state:', $formData);
												
												// Обновляем роль
												if (isset($formData['role'])) {
														$record->syncRoles([$formData['role']]);
														\Log::info('Role updated:', ['role' => $formData['role']]);
												}
												
												// Обновляем станции
												if (isset($formData['station_ids'])) {
														if (!empty($formData['station_ids'])) {
																\Log::info('Syncing stations:', ['ids' => $formData['station_ids']]);
																$record->stations()->sync($formData['station_ids']);
														} else {
																\Log::info('Detaching all stations');
																$record->stations()->detach();
														}
												}
										}),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
