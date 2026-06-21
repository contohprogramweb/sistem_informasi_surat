<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KlasifikasiArsip;

class KlasifikasiArsipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Level 1 - Kategori Utama
        $umum = KlasifikasiArsip::create([
            'parent_id' => null,
            'kode' => 'UM',
            'nama' => 'Umum',
            'deskripsi' => 'Surat-surat Umum',
            'level' => 1,
            'retensi_aktif' => 2,
            'retensi_inaktif' => 5,
            'is_active' => true,
        ]);

        $kepegawaian = KlasifikasiArsip::create([
            'parent_id' => null,
            'kode' => 'KP',
            'nama' => 'Kepegawaian',
            'deskripsi' => 'Surat-surat Kepegawaian',
            'level' => 1,
            'retensi_aktif' => 5,
            'retensi_inaktif' => 10,
            'is_active' => true,
        ]);

        $keuangan = KlasifikasiArsip::create([
            'parent_id' => null,
            'kode' => 'KU',
            'nama' => 'Keuangan',
            'deskripsi' => 'Surat-surat Keuangan',
            'level' => 1,
            'retensi_aktif' => 5,
            'retensi_inaktif' => 10,
            'is_active' => true,
        ]);

        $kurikulum = KlasifikasiArsip::create([
            'parent_id' => null,
            'kode' => 'KR',
            'nama' => 'Kurikulum',
            'deskripsi' => 'Surat-surat Kurikulum',
            'level' => 1,
            'retensi_aktif' => 3,
            'retensi_inaktif' => 8,
            'is_active' => true,
        ]);

        $kesiswaan = KlasifikasiArsip::create([
            'parent_id' => null,
            'kode' => 'KS',
            'nama' => 'Kesiswaan',
            'deskripsi' => 'Surat-surat Kesiswaan',
            'level' => 1,
            'retensi_aktif' => 3,
            'retensi_inaktif' => 8,
            'is_active' => true,
        ]);

        // Level 2 - Sub Kategori
        KlasifikasiArsip::create([
            'parent_id' => $umum->id,
            'kode' => 'UM.01',
            'nama' => 'Surat Masuk',
            'deskripsi' => 'Arsip surat masuk umum',
            'level' => 2,
            'retensi_aktif' => 2,
            'retensi_inaktif' => 5,
            'is_active' => true,
        ]);

        KlasifikasiArsip::create([
            'parent_id' => $umum->id,
            'kode' => 'UM.02',
            'nama' => 'Surat Keluar',
            'deskripsi' => 'Arsip surat keluar umum',
            'level' => 2,
            'retensi_aktif' => 2,
            'retensi_inaktif' => 5,
            'is_active' => true,
        ]);

        KlasifikasiArsip::create([
            'parent_id' => $kepegawaian->id,
            'kode' => 'KP.01',
            'nama' => 'Pangkat dan Golongan',
            'deskripsi' => 'Arsip pangkat dan golongan',
            'level' => 2,
            'retensi_aktif' => 5,
            'retensi_inaktif' => 10,
            'is_active' => true,
        ]);

        KlasifikasiArsip::create([
            'parent_id' => $keuangan->id,
            'kode' => 'KU.01',
            'nama' => 'Anggaran',
            'deskripsi' => 'Arsip anggaran',
            'level' => 2,
            'retensi_aktif' => 5,
            'retensi_inaktif' => 10,
            'is_active' => true,
        ]);

        // Level 3 - Sub Sub Kategori
        $pangkatGolongan = KlasifikasiArsip::where('kode', 'KP.01')->first();
        KlasifikasiArsip::create([
            'parent_id' => $pangkatGolongan->id,
            'kode' => 'KP.01.01',
            'nama' => 'Kenaikan Pangkat',
            'deskripsi' => 'Arsip kenaikan pangkat',
            'level' => 3,
            'retensi_aktif' => 5,
            'retensi_inaktif' => 10,
            'is_active' => true,
        ]);

        KlasifikasiArsip::create([
            'parent_id' => $pangkatGolongan->id,
            'kode' => 'KP.01.02',
            'nama' => 'Kenaikan Gaji Berkala',
            'deskripsi' => 'Arsip kenaikan gaji berkala',
            'level' => 3,
            'retensi_aktif' => 5,
            'retensi_inaktif' => 10,
            'is_active' => true,
        ]);

        // Level 4
        $kenaikanPangkat = KlasifikasiArsip::where('kode', 'KP.01.01')->first();
        KlasifikasiArsip::create([
            'parent_id' => $kenaikanPangkat->id,
            'kode' => 'KP.01.01.01',
            'nama' => 'SK Pangkat',
            'deskripsi' => 'Arsip SK kenaikan pangkat',
            'level' => 4,
            'retensi_aktif' => 5,
            'retensi_inaktif' => 10,
            'is_active' => true,
        ]);

        // Level 5
        $skPangkat = KlasifikasiArsip::where('kode', 'KP.01.01.01')->first();
        KlasifikasiArsip::create([
            'parent_id' => $skPangkat->id,
            'kode' => 'KP.01.01.01.01',
            'nama' => 'Berkas Pendukung SK Pangkat',
            'deskripsi' => 'Arsip berkas pendukung SK pangkat',
            'level' => 5,
            'retensi_aktif' => 5,
            'retensi_inaktif' => 10,
            'is_active' => true,
        ]);
    }
}
