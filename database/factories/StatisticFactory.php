<?php

namespace Database\Factories;

use App\Models\Station;
use App\Models\Statistic;
use Illuminate\Database\Eloquent\Factories\Factory;

class StatisticFactory extends Factory
{
    protected $model = Statistic::class;

    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'date' => fake()->date(),
            'data' => [
                'column_1' => fake()->randomFloat(2, 0, 100),
                'column_2' => fake()->randomFloat(2, 0, 100),
                'total' => fake()->randomFloat(2, 0, 200),
            ],
        ];
    }
}
