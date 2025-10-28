<?php

namespace App\Http\Controllers;

use App\Models\Statistic;
use App\Models\Station;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class StationDataApiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        if ($request->query('endpoint') && $request->query('endpoint') !== 'receive-data') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unsupported endpoint value.',
            ], 400);
        }

        $validated = $request->validate([
            'station' => ['required', 'array', 'min:1'],
            'station.0' => ['required'],
            'wash_date' => ['required', 'date'],
            'program_kg' => ['nullable', 'array'],
            'program_kg.*' => ['numeric'],
            'liquid_liter' => ['nullable', 'array'],
            'liquid_liter.*' => ['numeric'],
            'machine_kg' => ['nullable', 'array'],
            'machine_kg.*' => ['numeric'],
        ]);

        $stationCode = (string) Arr::first($validated['station']);

        $station = Station::where('code', $stationCode)->first();

        if (! $station) {
            return response()->json([
                'status' => 'error',
                'message' => 'Station not found.',
            ], 404);
        }

        $washDate = Carbon::parse($validated['wash_date'])->startOfDay();

        $programKg = array_values($validated['program_kg'] ?? []);
        $liquidLiters = array_values($validated['liquid_liter'] ?? []);
        $machineKg = array_values($validated['machine_kg'] ?? []);

        $statisticPayload = $this->buildStatisticPayload($programKg, $liquidLiters, $machineKg);

        Statistic::updateOrCreate(
            [
                'station_id' => $station->id,
                'date' => $washDate->toDateString(),
            ],
            [
                'data' => $statisticPayload,
            ],
        );

        return response()->json([
            'status' => 'ok',
            'message' => 'Statistic saved.',
        ]);
    }

    /**
     * @param  array<int, float|int|string|null>  $programKg
     * @param  array<int, float|int|string|null>  $liquidLiters
     * @param  array<int, float|int|string|null>  $machineKg
     * @return array<string, float|int|string|null>
     */
    protected function buildStatisticPayload(array $programKg, array $liquidLiters, array $machineKg): array
    {
        $payload = [];

        $programTotal = 0;
        foreach ($programKg as $index => $value) {
            $key = 'column_' . ($index + 1);
            $numeric = is_numeric($value) ? (float) $value : 0;
            $payload[$key] = $value;
            $programTotal += $numeric;
        }
        $payload['total'] = round($programTotal, 3);

        $litersTotal = 0;
        foreach ($liquidLiters as $index => $value) {
            $key = 'liters_' . ($index + 1);
            $numeric = is_numeric($value) ? (float) $value : 0;
            $payload[$key] = $value;
            $litersTotal += $numeric;
        }
        $payload['liters_total'] = round($litersTotal, 3);

        $kgTotal = 0;
        foreach ($machineKg as $index => $value) {
            $key = 'kg_' . ($index + 1);
            $numeric = is_numeric($value) ? (float) $value : 0;
            $payload[$key] = $value;
            $kgTotal += $numeric;
        }
        $payload['kg_total'] = round($kgTotal, 3);

        return $payload;
    }
}

