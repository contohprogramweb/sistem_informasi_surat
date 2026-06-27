<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_unit' => fake()->unique()->company() . ' Unit',
            'kode_unit' => fake()->unique()->bothify('???-###'),
            'deskripsi' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
