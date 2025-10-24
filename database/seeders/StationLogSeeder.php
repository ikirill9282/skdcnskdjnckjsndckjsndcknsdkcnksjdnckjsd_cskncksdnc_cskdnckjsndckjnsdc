<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\StationLog;
use Illuminate\Database\Seeder;

class StationLogSeeder extends Seeder
{
    public function run()
    {
        $station = Station::first();
        
        if ($station) {
            StationLog::create([
                'station_id' => $station->id,
                'event_type' => 'последнее средство',
                'washing_machine_number' => 2,
                'program_number' => 7,
                'white_loading' => 40,
                'signal_1' => 0,
                'machine_signals' => [74, 0, 0, 0, 0, 0],
                'signal_2' => 0,
                'detergent_signals' => [0, 0, 0, 140, 0, 0, 0, 0],
            ]);

            StationLog::create([
                'station_id' => $station->id,
                'event_type' => 'подача средства',
                'washing_machine_number' => 0,
                'program_number' => 0,
                'white_loading' => 0,
                'signal_1' => 0,
                'machine_signals' => [0, 0, 42, 0, 0, 0],
                'signal_2' => 0,
                'detergent_signals' => [0, 400, 0, 0, 0, 0, 0, 0],
            ]);
        }
    }
}
