<?php

namespace Database\Factories;

use App\Models\SuratMasuk;
use App\Models\KlasifikasiArsip;
use App\Models\SifatSurat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SuratMasuk>
 */
class SuratMasukFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'agenda' => fake()->unique()->bothify('AG-####'),
            'tanggal_terima' => fake()->dateTimeBetween('-1 year', 'now'),
            'cara_terima' => fake()->randomElement(['Langsung', 'Pos', 'Email', 'Faksimili', 'E-Kantor']),
            'penerima_fisik' => fake()->name(),
            'nomor_surat' => fake()->unique()->bothify('###/TEST/2024'),
            'tanggal_surat' => fake()->dateTimeBetween('-1 year', 'now'),
            'pengirim' => fake()->company(),
            'perihal' => fake()->sentence(),
            'ringkasan' => fake()->paragraph(),
            'klasifikasi_id' => KlasifikasiArsip::factory(),
            'sifat_id' => SifatSurat::factory(),
            'prioritas' => fake()->randomElement(['Rendah', 'Normal', 'Tinggi', 'Segera']),
            'indeks' => [],
            'tidak_perlu_disposisi' => false,
            'status' => 'Aktif',
        ];
    }

    /**
     * Indicate that the surat masuk does not need disposisi.
     */
    public function tanpaDisposisi(): static
    {
        return $this->state(fn (array $attributes) => [
            'tidak_perlu_disposisi' => true,
        ]);
    }

    /**
     * Indicate that the surat masuk is archived.
     */
    public function diarsipkan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Diarsipkan',
        ]);
    }

    /**
     * Indicate that the surat masuk is deleted.
     */
    public function dihapus(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Dihapus',
        ]);
    }
}
