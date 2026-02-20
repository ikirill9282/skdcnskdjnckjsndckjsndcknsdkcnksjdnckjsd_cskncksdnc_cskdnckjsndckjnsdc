<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

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
}
