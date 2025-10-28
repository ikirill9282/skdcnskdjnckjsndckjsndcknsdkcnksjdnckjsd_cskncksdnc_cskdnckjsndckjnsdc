<?php

use App\Http\Controllers\StationDataApiController;
use App\Http\Controllers\StationEventApiController;
use App\Http\Controllers\StationSettingsApiController;
use Illuminate\Support\Facades\Route;

Route::post('/work/api.php', StationDataApiController::class)->name('api.stations.receive-data');

Route::post('/work/api-setting.php', [StationSettingsApiController::class, 'store'])->name('api.stations.receive-settings');

Route::get('/work/api-setting.php', [StationSettingsApiController::class, 'show'])->name('api.stations.get-settings');

Route::post('/work/api-events.php', StationEventApiController::class)->name('api.stations.station-event');

