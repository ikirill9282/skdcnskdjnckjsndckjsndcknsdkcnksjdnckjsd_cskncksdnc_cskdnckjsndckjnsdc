<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\Company;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var User|null $actor */
        $actor = auth()->user();

        abort_unless($actor && UserResource::canCreate(), 403);

        $this->guardPayload($actor, $data);

        return $data;
    }

    protected function afterCreate(): void
    {
        $companyId = $this->data['company_id'] ?? null;
        if ($companyId) {
            $this->record->companies()->sync([$companyId]);
        }

        $role = $this->data['role'] ?? null;
        if ($role) {
            $this->record->assignRole($role);
        }
    }

    protected function guardPayload(User $actor, array $data): void
    {
        $role = (string) ($data['role'] ?? '');
        $companyId = isset($data['company_id']) ? (int) $data['company_id'] : null;
        $rolesRequiringCompany = ['admin', 'company-admin', 'manager', 'client'];

        if (! in_array($role, ['super-admin', 'admin', 'company-admin', 'manager', 'client'], true)) {
            throw ValidationException::withMessages([
                'role' => 'Недопустимая роль.',
            ]);
        }

        if (in_array($role, $rolesRequiringCompany, true) && ! $companyId) {
            throw ValidationException::withMessages([
                'company_id' => 'Для выбранной роли требуется компания.',
            ]);
        }

        if ($companyId && ! Company::query()->whereKey($companyId)->exists()) {
            throw ValidationException::withMessages([
                'company_id' => 'Компания не найдена.',
            ]);
        }

        if (! $actor->isBusinessAdmin()) {
            return;
        }

        $allowedCompanyIds = $actor->businessCompanyIds();

        if (empty($allowedCompanyIds)) {
            abort(403);
        }

        if (! in_array($role, ['manager', 'client'], true)) {
            throw ValidationException::withMessages([
                'role' => 'Можно создавать только роли manager и client.',
            ]);
        }

        if (! $companyId || ! in_array($companyId, $allowedCompanyIds, true)) {
            throw ValidationException::withMessages([
                'company_id' => 'Можно выбрать только компанию из вашего доступа.',
            ]);
        }
    }
}
