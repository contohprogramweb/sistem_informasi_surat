<?php

namespace Database\Factories;

use App\Models\SuratKeluar;
use App\Models\Unit;
use App\Models\KlasifikasiArsip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SuratKeluar>
 */
class SuratKeluarFactory extends Factory
{
    protected $model = SuratKeluar::class;

    public function definition(): array
    {
        return [
            'nomor_surat' => 'SK/' . fake()->unique()->randomNumber(4) . '/' . date('Y'),
            'tanggal_surat' => fake()->dateTimeBetween('-1 year', 'now'),
            'tujuan' => fake()->company(),
            'perihal' => fake()->sentence(6),
            'unit_id' => Unit::factory(),
            'klasifikasi_id' => KlasifikasiArsip::factory(),
            'created_by' => User::factory(),
            'status' => fake()->randomElement(['draft', 'review', 'disetujui', 'siap_ttd', 'tertandatangani', 'terkirim']),
            'sifat' => fake()->randomElement(['biasa', 'penting', 'rahasia']),
            'isi_ringkas' => fake()->optional()->paragraph(),
        ];
    }

    public function terkirim(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'terkirim',
            'sent_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    public function tertandatangani(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'tertandatangani',
            'signed_at' => now(),
            'signed_by' => User::factory()->create(['role' => 'admin'])->id,
        ]);
    }
}
