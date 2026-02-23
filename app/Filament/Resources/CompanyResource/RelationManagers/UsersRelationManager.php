<?php

namespace App\Filament\Resources\CompanyResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Пользователи компании';

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
                    ->label('Email'),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255)
                    ->label('Пароль'),

                Forms\Components\Select::make('roles')
                    ->label('Роль')
                    ->options(fn (): array => $this->roleOptions())
                    ->multiple()
                    ->required(),

                Forms\Components\Select::make('stations')
                    ->label('Станции')
                    ->relationship('stations', 'name', function ($query) {
                        // Показываем только станции текущей компании
                        return $query->where('company_id', $this->ownerRecord->id);
                    })
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Имя'),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->label('Роли'),
                Tables\Columns\TextColumn::make('stations.name')
                    ->badge()
                    ->label('Станции')
                    ->separator(','),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Хешируем пароль
                        if (isset($data['password'])) {
                            $data['password'] = bcrypt($data['password']);
                        }
                        return $data;
                    })
                    ->after(function ($record, array $data) {
                        // Назначаем роли
                        if (isset($data['roles'])) {
                            $record->syncRoles($data['roles']);
                        }

                        // Привязываем станции
                        if (isset($data['stations'])) {
                            $record->stations()->sync($data['stations']);
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        // Удаляем пароль, если он не заполнен
                        if (empty($data['password'])) {
                            unset($data['password']);
                        } else {
                            $data['password'] = bcrypt($data['password']);
                        }
                        return $data;
                    })
                    ->after(function ($record, array $data) {
                        // Синхронизируем роли
                        if (isset($data['roles'])) {
                            $record->syncRoles($data['roles']);
                        }

                        // Синхронизируем станции
                        if (isset($data['stations'])) {
                            $record->stations()->sync($data['stations']);
                        }
                    })
                    ->mutateRecordDataUsing(function ($record, array $data): array {
                        // Загружаем текущие роли и станции
                        $data['roles'] = $record->roles->pluck('name')->toArray();
                        $data['stations'] = $record->stations->pluck('id')->toArray();
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<string, string>
     */
    protected function roleOptions(): array
    {
        $user = auth()->user();

        if (! $user) {
            return [];
        }

        if ($user->isSuperAdmin()) {
            return [
                'admin' => 'Администратор',
                'company-admin' => 'Администратор компании',
                'manager' => 'Менеджер',
                'client' => 'Клиент',
            ];
        }

        if ($user->isBusinessAdmin()) {
            return [
                'manager' => 'Менеджер',
                'client' => 'Клиент',
            ];
        }

        return [];
    }
}
