<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Station;
use Illuminate\Database\Eloquent\Factories\Factory;

class StationFactory extends Factory
{
    protected $model = Station::class;

    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('STN-####'),
            'name' => fake()->words(2, true),
            'region' => fake()->city(),
            'company_id' => Company::factory(),
            'is_active' => true,
        ];
    }
}
