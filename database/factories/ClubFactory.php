<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Club>
 */
class ClubFactory extends Factory
{
    protected $model = Club::class;

    public function definition(): array
    {
        return [
            'region_id' => Region::factory(),
            'name' => fake()->unique()->company() . ' Eagles Club',
        ];
    }
}
