<?php

namespace App\Filament\Resources\StationResource\Pages;

use App\Filament\Resources\StationResource;
use Illuminate\Support\Facades\Auth;

trait EnsuresStationManagementAccess
{
    protected function ensureStationManagementAccess(): void
    {
        abort_unless(StationResource::userHasStationAccess($this->record), 403);

        abort_if(Auth::user()?->isClient(), 403);
    }
}
