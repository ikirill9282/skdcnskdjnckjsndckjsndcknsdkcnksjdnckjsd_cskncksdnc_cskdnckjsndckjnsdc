<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\Statistic;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class StatisticSeeder extends Seeder
{
    public function run()
    {
        $station = Station::first();
        
        if ($station) {
            Statistic::create([
                'station_id' => $station->id,
                'date' => Carbon::parse('2025-10-21'),
                'data' => [
                    'column_1' => 5,
                    'column_2' => 3,
                    'column_3' => 8,
                    'column_4' => 2,
                    'column_5' => 6,
                    'column_6' => 4,
                    'column_7' => 7,
                    'column_8' => 1,
                    'column_9' => 9,
                    'column_10' => 3,
                    'column_11' => 5,
                    'column_12' => 2,
                    'column_13' => 0,
                    'column_14' => 0,
                    'column_15' => 0,
                    'column_16' => 0,
                    'column_17' => 0,
                    'column_18' => 0,
                    'column_19' => 0,
                    'total' => 55,
                ],
            ]);
        }
    }
}
