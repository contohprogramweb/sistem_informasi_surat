<?php

namespace Database\Factories;

use App\Models\SifatSurat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SifatSurat>
 */
class SifatSuratFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->randomElement(['Biasa', 'Penting', 'Segera', 'Rahasia']),
            'keterangan' => fake()->sentence(),
        ];
    }
}
