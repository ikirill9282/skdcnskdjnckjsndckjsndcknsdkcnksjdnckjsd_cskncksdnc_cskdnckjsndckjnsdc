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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Пользователи';

    public static function shouldRegisterNavigation(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isBusinessAdmin()) {
            return $user->businessCompanyIds() !== [];
        }

        return false;
    }

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isBusinessAdmin()) {
            return $user->businessCompanyIds() !== [];
        }

        return false;
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
                    ->options(fn (): array => static::availableRoleOptions())
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($state, callable $set) => $set('role', $state)),

                Forms\Components\Select::make('company_id')
                    ->label('Компания')
                    ->options(fn (): array => static::companyOptionsForActor())
                    ->searchable()
                    ->preload()
                    ->visible(fn (callable $get): bool => in_array($get('role'), ['admin', 'company-admin', 'manager', 'client'], true))
                    ->required(fn (callable $get): bool => in_array($get('role'), ['admin', 'company-admin', 'manager', 'client'], true)),
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

                Tables\Columns\TextColumn::make('companies.name')
                    ->badge()
                    ->listWithLineBreaks()
                    ->placeholder('Без компании')
                    ->label('Компания'),

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
                        'super-admin' => 'Супер-администратор',
                        'admin' => 'Администратор',
                        'company-admin' => 'Администратор компании',
                        'manager' => 'Менеджер',
                        'client' => 'Клиент',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $role = $data['value'] ?? null;

                        if (blank($role)) {
                            return $query;
                        }

                        return $query->whereHas('roles', function (Builder $rolesQuery) use ($role): void {
                            $rolesQuery->where('name', $role);
                        });
                    })
                    ->label('Фильтр по роли'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (User $record): bool => static::canEdit($record)),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn (User $record): bool => static::canDelete($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn (): bool => static::canDeleteAny()),
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

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['roles', 'companies']);

        /** @var User|null $actor */
        $actor = auth()->user();

        if (! $actor) {
            return $query->whereRaw('1 = 0');
        }

        if ($actor->isSuperAdmin()) {
            return $query;
        }

        if ($actor->isBusinessAdmin()) {
            $companyIds = $actor->businessCompanyIds();

            if (empty($companyIds)) {
                return $query->whereRaw('1 = 0');
            }

            return $query
                ->whereHas('roles', function (Builder $roleQuery): void {
                    $roleQuery->whereIn('name', ['manager', 'client']);
                })
                ->whereDoesntHave('roles', function (Builder $roleQuery): void {
                    $roleQuery->whereIn('name', ['super-admin', 'admin', 'company-admin']);
                })
                ->whereHas('companies', function (Builder $companyQuery) use ($companyIds): void {
                    $companyQuery->whereIn('companies.id', $companyIds);
                });
        }

        return $query->whereRaw('1 = 0');
    }

    public static function canCreate(): bool
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        if (! $actor) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($actor->isBusinessAdmin()) {
            return $actor->businessCompanyIds() !== [];
        }

        return false;
    }

    public static function canEdit(Model $record): bool
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        if (! $actor || ! $record instanceof User) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        return static::isBusinessAdminTargetManageable($actor, $record);
    }

    public static function canDelete(Model $record): bool
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        if (! $actor || ! $record instanceof User) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        return static::isBusinessAdminTargetManageable($actor, $record);
    }

    public static function canDeleteAny(): bool
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        if (! $actor) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        if ($actor->isBusinessAdmin()) {
            return $actor->businessCompanyIds() !== [];
        }

        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected static function availableRoleOptions(): array
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        if (! $actor) {
            return [];
        }

        if ($actor->isSuperAdmin()) {
            return [
                'super-admin' => 'Супер-администратор',
                'admin' => 'Администратор',
                'company-admin' => 'Администратор компании',
                'manager' => 'Менеджер',
                'client' => 'Клиент',
            ];
        }

        if ($actor->isBusinessAdmin() && $actor->businessCompanyIds() !== []) {
            return [
                'manager' => 'Менеджер',
                'client' => 'Клиент',
            ];
        }

        return [];
    }

    /**
     * @return array<int|string, string>
     */
    protected static function companyOptionsForActor(): array
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        if (! $actor) {
            return [];
        }

        if ($actor->isSuperAdmin()) {
            return Company::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        if ($actor->isBusinessAdmin()) {
            $companyIds = $actor->businessCompanyIds();

            if (empty($companyIds)) {
                return [];
            }

            return Company::query()
                ->whereIn('id', $companyIds)
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        }

        return [];
    }

    protected static function isBusinessAdminTargetManageable(User $actor, User $target): bool
    {
        if (! $actor->isBusinessAdmin()) {
            return false;
        }

        $companyIds = $actor->businessCompanyIds();

        if (empty($companyIds)) {
            return false;
        }

        if (! $target->hasAnyRole(['manager', 'client'])) {
            return false;
        }

        if ($target->hasAnyRole(['super-admin', 'admin', 'company-admin'])) {
            return false;
        }

        return $target->companies()
            ->whereIn('companies.id', $companyIds)
            ->exists();
    }
}
