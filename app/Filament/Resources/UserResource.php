<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Company;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $navigationLabel = 'Пользователи';

    // Скрывает пункт меню от пользователей с ролью 'user'
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->hasAnyRole(['super-admin', 'admin']);
    }

    // Блокирует прямой доступ по URL для роли 'user'
    public static function canAccess(): bool
    {
        return auth()->user()->hasAnyRole(['super-admin', 'admin', 'company-admin', 'manager', 'client']);
    }

    public static function form(Form $form): Form
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
										->unique(ignoreRecord: true)
										->maxLength(255)
										->label('Email'),
								
								Forms\Components\TextInput::make('password')
										->password()
										->required(fn (string $context): bool => $context === 'create')
										->maxLength(255)
										->label('Пароль'),
								
								Forms\Components\Select::make('role')
										->label('Роль')
										->options([
												'super-admin' => 'Супер-администратор',
												'company-admin' => 'Администратор компании',
												'manager' => 'Менеджер',
												'client' => 'Клиент',
										])
										->required()
										->reactive()
										->afterStateUpdated(fn ($state, callable $set) => $set('role', $state)),
								
								Forms\Components\Select::make('company_id')
										->label('Компания')
										->options(Company::pluck('name', 'id'))
										->searchable()
										->visible(fn (callable $get) => in_array($get('role'), ['company-admin', 'manager', 'client']))
										->required(fn (callable $get) => in_array($get('role'), ['company-admin', 'manager', 'client'])),
						]);
		}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Имя'),
                    
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('Email'),
                    
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->label('Роли'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label('Создан'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'super-admin' => 'Super Admin',
                        'admin' => 'Admin',
                        'user' => 'User',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value']) {
                            return $query->whereHas('roles', function ($query) use ($data) {
                                $query->where('name', $data['value']);
                            });
                        }
                    })
                    ->label('Фильтр по роли'),
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
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
