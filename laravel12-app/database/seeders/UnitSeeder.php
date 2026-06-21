<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Unit;

class UnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = [
            [
                'kode_unit' => 'TU',
                'nama_unit' => 'Tata Usaha',
                'deskripsi' => 'Unit Tata Usaha - Administrasi Umum',
                'is_active' => true,
            ],
            [
                'kode_unit' => 'KUR',
                'nama_unit' => 'Kurikulum',
                'deskripsi' => 'Unit Kurikulum - Pengelolaan Pembelajaran',
                'is_active' => true,
            ],
            [
                'kode_unit' => 'KES',
                'nama_unit' => 'Kesiswaan',
                'deskripsi' => 'Unit Kesiswaan - Pengelolaan Siswa',
                'is_active' => true,
            ],
        ];

        foreach ($units as $unit) {
            Unit::updateOrCreate(
                ['kode_unit' => $unit['kode_unit']],
                $unit
            );
        }
    }
}
