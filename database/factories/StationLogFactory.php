<?php

namespace Database\Factories;

use App\Models\Station;
use App\Models\StationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class StationLogFactory extends Factory
{
    protected $model = StationLog::class;

    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'event_type' => fake()->randomElement(['start', 'stop', 'error', 'done']),
            'washing_machine_number' => fake()->numberBetween(1, 6),
            'program_number' => fake()->numberBetween(1, 19),
            'white_loading' => fake()->randomFloat(2, 0, 100),
            'signal_1' => fake()->randomFloat(2, 0, 10),
            'signal_2' => fake()->randomFloat(2, 0, 10),
            'machine_signals' => [1, 0, 1, 0, 1, 0],
            'detergent_signals' => [1, 0, 1, 0, 1, 0, 1, 0],
            'comment' => null,
        ];
    }
}
