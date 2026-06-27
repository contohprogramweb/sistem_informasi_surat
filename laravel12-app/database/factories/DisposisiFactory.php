<?php

namespace Database\Factories;

use App\Models\Disposisi;
use App\Models\SuratMasuk;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Disposisi>
 */
class DisposisiFactory extends Factory
{
    protected $model = Disposisi::class;

    public function definition(): array
    {
        return [
            'surat_masuk_id' => SuratMasuk::factory(),
            'unit_tujuan_id' => Unit::factory(),
            'instruksi' => fake()->sentence(5),
            'sifat' => fake()->randomElement(['Biasa', 'Penting', 'Segera']),
            'catatan' => fake()->optional()->sentence(3),
            'tanggal_disposisi' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    public function segera(): static
    {
        return $this->state(fn (array $attributes) => [
            'sifat' => 'Segera',
        ]);
    }

    public function penting(): static
    {
        return $this->state(fn (array $attributes) => [
            'sifat' => 'Penting',
        ]);
    }

    public function biasa(): static
    {
        return $this->state(fn (array $attributes) => [
            'sifat' => 'Biasa',
        ]);
    }
}
