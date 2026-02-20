<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['company_id'] = $this->record->companies()->first()?->id;
        $data['role'] = $this->record->roles->first()?->name;

        return $data;
    }

    protected function afterSave(): void
    {
        $companyId = $this->data['company_id'] ?? null;
        if ($companyId) {
            $this->record->companies()->sync([$companyId]);
        } else {
            $this->record->companies()->detach();
        }

        $role = $this->data['role'] ?? null;
        if ($role) {
            $this->record->syncRoles([$role]);
        }
    }
}
