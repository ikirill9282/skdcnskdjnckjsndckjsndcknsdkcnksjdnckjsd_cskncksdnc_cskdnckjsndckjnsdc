<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\StationLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class StationEventApiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($request->query('endpoint') && $request->query('endpoint') !== 'station-event') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unsupported endpoint value.',
            ], 400);
        }

        $validated = $request->validate([
            'station' => ['required', 'array', 'min:1'],
            'station.0' => ['required'],
            'event_type' => ['required', 'string', 'max:255'],
            'washing_machine_number' => ['nullable', 'integer'],
            'program_number' => ['nullable', 'integer'],
            'white_loading' => ['nullable', 'numeric'],
            'signal_1' => ['nullable', 'numeric'],
            'signal_2' => ['nullable', 'numeric'],
            'machine_signals' => ['nullable', 'array'],
            'detergent_signals' => ['nullable', 'array'],
            'comment' => ['nullable', 'string'],
            'occurred_at' => ['nullable', 'date'],
        ]);

        $stationCode = (string) Arr::first($validated['station']);

        $station = Station::where('code', $stationCode)->first();

        if (! $station) {
            return response()->json([
                'status' => 'error',
                'message' => 'Station not found.',
            ], 404);
        }

        $logData = [
            'station_id' => $station->id,
            'event_type' => $validated['event_type'],
            'washing_machine_number' => $validated['washing_machine_number'] ?? null,
            'program_number' => $validated['program_number'] ?? null,
            'white_loading' => $validated['white_loading'] ?? null,
            'signal_1' => $validated['signal_1'] ?? null,
            'signal_2' => $validated['signal_2'] ?? null,
            'machine_signals' => $validated['machine_signals'] ?? null,
            'detergent_signals' => $validated['detergent_signals'] ?? null,
            'comment' => $validated['comment'] ?? null,
        ];

        $log = StationLog::create($logData);

        if (! empty($validated['occurred_at'])) {
            $occurredAt = Carbon::parse($validated['occurred_at']);
            $log->forceFill([
                'created_at' => $occurredAt,
                'updated_at' => $occurredAt,
            ])->save();
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Event stored.',
        ]);
    }
}

