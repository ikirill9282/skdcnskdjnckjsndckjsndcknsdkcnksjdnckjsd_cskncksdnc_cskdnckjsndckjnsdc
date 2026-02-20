<?php

namespace Database\Factories;

use App\Models\Program;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'program_number' => fake()->numberBetween(1, 19),
            'name' => 'Программа ' . fake()->numberBetween(1, 19),
        ];
    }
}
