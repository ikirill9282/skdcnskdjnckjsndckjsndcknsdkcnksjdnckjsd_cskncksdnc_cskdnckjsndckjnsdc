<?php

namespace Database\Seeders;

use App\Models\Station;
use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run()
    {
        $stations = Station::all();
        
        foreach ($stations as $station) {
            // Создаём 19 программ для каждой станции
            for ($i = 1; $i <= 19; $i++) {
                Program::create([
                    'station_id' => $station->id,
                    'program_number' => $i,
                    'name' => "Программа $i",
                ]);
            }
        }
    }
}
