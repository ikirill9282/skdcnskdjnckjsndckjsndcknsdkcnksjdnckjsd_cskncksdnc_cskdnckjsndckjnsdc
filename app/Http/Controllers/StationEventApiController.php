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
            'event_type' => ['required_without:event', 'string', 'max:255'],
            'event' => ['required_without:event_type', 'string', 'max:255'],
            'washing_machine_number' => ['nullable', 'integer'],
            'program_number' => ['nullable', 'integer'],
            'white_loading' => ['nullable', 'numeric'],
            'signal_1' => ['nullable', 'numeric'],
            'signal_2' => ['nullable', 'numeric'],
            'machine_signals' => ['nullable', 'array'],
            'detergent_signals' => ['nullable', 'array'],
            'comment' => ['nullable', 'string'],
            'occurred_at' => ['nullable', 'date'],
            'date' => ['nullable', 'string'],
            'done' => ['nullable', 'array'],
            'done.*' => ['nullable', 'numeric'],
            'sitr' => ['nullable', 'array'],
            'sitr.*' => ['nullable', 'numeric'],
            'deterg' => ['nullable', 'array'],
            'deterg.*' => ['nullable', 'numeric'],
        ]);

        $stationCode = (string) Arr::first($validated['station']);

        $station = Station::where('code', $stationCode)->first();

        if (! $station) {
            return response()->json([
                'status' => 'error',
                'message' => 'Station not found.',
            ], 404);
        }

        $eventType = $validated['event'] ?? $validated['event_type'] ?? 'event';

        $washingMachineNumber = $validated['washing_machine_number']
            ?? ($validated['done'][0] ?? null);

        $programNumber = $validated['program_number']
            ?? ($validated['done'][1] ?? null);

        $whiteLoading = $validated['white_loading']
            ?? ($validated['done'][2] ?? null);

        $machineSignals = $validated['machine_signals']
            ?? ($validated['sitr'] ?? null);

        $detergentSignals = $validated['detergent_signals']
            ?? ($validated['deterg'] ?? null);

        $logData = [
            'station_id' => $station->id,
            'event_type' => $eventType,
            'washing_machine_number' => $washingMachineNumber,
            'program_number' => $programNumber,
            'white_loading' => $whiteLoading,
            'signal_1' => $validated['signal_1'] ?? null,
            'signal_2' => $validated['signal_2'] ?? null,
            'machine_signals' => $machineSignals,
            'detergent_signals' => $detergentSignals,
            'comment' => $validated['comment'] ?? null,
        ];

        $log = StationLog::create($logData);

        $occurredAt = null;

        if (! empty($validated['occurred_at'])) {
            $occurredAt = Carbon::parse($validated['occurred_at']);
        } elseif (! empty($validated['date'])) {
            $occurredAt = $this->parseAlternateDate($validated['date']);
        }

        if ($occurredAt) {
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

    protected function parseAlternateDate(?string $date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d-H-i-s', $date);
        } catch (\Exception $e) {
            return null;
        }
    }
}

