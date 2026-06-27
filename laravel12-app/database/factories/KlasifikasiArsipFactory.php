<?php

namespace Database\Factories;

use App\Models\KlasifikasiArsip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KlasifikasiArsip>
 */
class KlasifikasiArsipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'kode' => fake()->unique()->bothify('KL-###'),
            'nama' => fake()->unique()->words(3, true),
            'parent_id' => null,
            'retensi_aktif' => fake()->numberBetween(1, 5),
            'retensi_inaktif' => fake()->numberBetween(1, 10),
        ];
    }

    /**
     * Indicate that the klasifikasi has a parent.
     */
    public function withParent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => KlasifikasiArsip::factory(),
        ]);
    }
}
