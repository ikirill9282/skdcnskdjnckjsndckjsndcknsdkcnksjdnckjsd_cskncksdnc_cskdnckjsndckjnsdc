<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\StationSettingValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class StationSettingsApiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if ($request->query('endpoint') && $request->query('endpoint') !== 'receive-settings') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unsupported endpoint value.',
            ], 400);
        }

        $payload = $request->json()->all();

        if (empty($payload)) {
            $raw = trim($request->getContent() ?? '');

            if ($raw !== '' && str_starts_with($raw, '[')) {
                $decoded = json_decode($raw, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $payload = $decoded;
                }
            }
        }

        if (is_array($payload) && Arr::isList($payload)) {
            return $this->storeCompactPayload($payload);
        }

        $validated = $request->validate([
            'station' => ['required', 'array', 'min:1'],
            'station.0' => ['required'],
            'machines' => ['nullable', 'array'],
            'detergents' => ['nullable', 'array'],
            'auto_programs' => ['nullable', 'array'],
            'manual_programs' => ['nullable', 'array'],
            'program_names' => ['nullable', 'array'],
            'current_status' => ['nullable', 'string'],
            'current_detergent' => ['nullable', 'string'],
            'current_volume' => ['nullable', 'numeric'],
            'current_washing_machine' => ['nullable', 'string'],
            'current_process_completion' => ['nullable', 'integer'],
            'activation_date' => ['nullable', 'date'],
            'service_date' => ['nullable', 'date'],
            'days_worked' => ['nullable', 'integer'],
            'warnings' => ['nullable', 'string'],
            'errors' => ['nullable', 'string'],
        ]);

        $stationCode = (string) Arr::first($validated['station']);

        $station = Station::where('code', $stationCode)->first();

        if (! $station) {
            return response()->json([
                'status' => 'error',
                'message' => 'Station not found.',
            ], 404);
        }

        $updates = [];

        $mapping = [
            'machines' => 'machines_data',
            'detergents' => 'detergents_data',
            'auto_programs' => 'auto_programs_data',
            'manual_programs' => 'manual_programs_data',
            'program_names' => 'program_names',
            'current_status' => 'current_status',
            'current_detergent' => 'current_detergent',
            'current_volume' => 'current_volume',
            'current_washing_machine' => 'current_washing_machine',
            'current_process_completion' => 'current_process_completion',
            'warnings' => 'warnings',
            'errors' => 'errors',
            'days_worked' => 'days_worked',
        ];

        foreach ($mapping as $inputKey => $column) {
            if (array_key_exists($inputKey, $validated)) {
                $updates[$column] = $validated[$inputKey];
            }
        }

        if (array_key_exists('activation_date', $validated)) {
            $updates['activation_date'] = $validated['activation_date']
                ? Carbon::parse($validated['activation_date'])->toDateString()
                : null;
        }

        if (array_key_exists('service_date', $validated)) {
            $updates['service_date'] = $validated['service_date']
                ? Carbon::parse($validated['service_date'])->toDateString()
                : null;
        }

        if (! empty($updates)) {
            $station->fill($updates)->save();
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Settings updated.',
        ]);
    }

    protected function storeCompactPayload(array $payload): JsonResponse
    {
        if (count($payload) < 3) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid payload size.',
            ], 422);
        }

        $stationCode = (string) array_shift($payload);
        $blockNumber = (int) array_shift($payload);
        $values = array_slice(array_values($payload), 0, 19);

        $station = Station::where('code', $stationCode)->first();

        if (! $station) {
            return response()->json([
                'status' => 'error',
                'message' => 'Station not found.',
            ], 404);
        }

        foreach ($values as $index => $value) {
            StationSettingValue::updateOrCreate(
                [
                    'station_id' => $station->id,
                    'block_number' => $blockNumber,
                    'setting_index' => $index + 1,
                ],
                [
                    'value' => is_scalar($value) ? (string) $value : json_encode($value),
                ],
            );
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Setting block stored.',
            'block' => [
                'station_code' => $stationCode,
                'block_number' => $blockNumber,
                'count' => count($values),
            ],
        ]);
    }

    public function show(Request $request): JsonResponse
    {
        if ($request->query('endpoint') && $request->query('endpoint') !== 'receive-settings-get') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unsupported endpoint value.',
            ], 400);
        }

        $stationIdentifier = $request->query('id');

        if (! $stationIdentifier) {
            return response()->json([
                'status' => 'error',
                'message' => 'Station identifier is required.',
            ], 422);
        }

        $station = Station::with('settingValues')
            ->where('code', (string) $stationIdentifier)
            ->first();

        if (! $station) {
            return response()->json([
                'status' => 'error',
                'message' => 'Station not found.',
            ], 404);
        }

        $settingBlocks = $station->settingValues
            ->sortBy(function ($item) {
                return sprintf('%05d-%02d', $item->block_number, $item->setting_index);
            })
            ->groupBy('block_number')
            ->map(function ($group) {
                return $group
                    ->sortBy('setting_index')
                    ->mapWithKeys(function ($item) {
                        return [$item->setting_index => $item->value];
                    })
                    ->toArray();
            })
            ->toArray();

        return response()->json([
            'status' => 'ok',
            'station' => [
                'code' => $station->code,
                'name' => $station->name,
                'region' => $station->region,
            ],
            'settings' => [
                'machines' => $station->machines_data ?? [],
                'detergents' => $station->detergents_data ?? [],
                'auto_programs' => $station->auto_programs_data ?? [],
                'manual_programs' => $station->manual_programs_data ?? [],
                'program_names' => $station->program_names ?? [],
                'current_status' => $station->current_status,
                'current_detergent' => $station->current_detergent,
                'current_volume' => $station->current_volume,
                'current_washing_machine' => $station->current_washing_machine,
                'current_process_completion' => $station->current_process_completion,
                'activation_date' => optional($station->activation_date)->toDateString(),
                'service_date' => optional($station->service_date)->toDateString(),
                'days_worked' => $station->days_worked,
                'warnings' => $station->warnings,
                'errors' => $station->errors,
                'setting_blocks' => $settingBlocks,
            ],
        ]);
    }
}
