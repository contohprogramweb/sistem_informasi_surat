<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Unit;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\Disposisi;
use App\Models\Delegasi;
use App\Models\SifatSurat;
use App\Models\KlasifikasiArsip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FinalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan roles dan permissions sudah ada
        $this->call([
            RolePermissionSeeder::class,
            UnitSeeder::class,
            SifatSuratSeeder::class,
            KlasifikasiArsipSeeder::class,
        ]);

        // Buat users untuk testing
        $this->createUsers();

        // Buat 10 surat masuk sample
        $this->createSuratMasuk();

        // Buat 5 surat keluar dengan alur lengkap
        $this->createSuratKeluar();

        // Buat 15 disposisi dengan tree structure
        $this->createDisposisi();

        // Buat 3 delegasi (2 aktif, 1 nonaktif)
        $this->createDelegasi();

        $this->command->info('Final seeding completed successfully!');
    }

    private function createUsers()
    {
        // Admin
        User::firstOrCreate(
            ['email' => 'admin@siapsmk.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'unit_id' => Unit::first()?->id ?? 1,
                'nip' => '198001012000011001',
                'jabatan' => 'Administrator Sistem',
                'is_active' => true,
            ]
        );

        // Staff TU
        User::firstOrCreate(
            ['email' => 'stafftu@siapsmk.local'],
            [
                'name' => 'Staff Tata Usaha',
                'password' => Hash::make('password123'),
                'role' => 'staff_tu',
                'unit_id' => Unit::first()?->id ?? 1,
                'nip' => '198502022005012002',
                'jabatan' => 'Staff Tata Usaha',
                'is_active' => true,
            ]
        );

        // Pimpinan
        User::firstOrCreate(
            ['email' => 'pimpinan@siapsmk.local'],
            [
                'name' => 'Kepala Sekolah',
                'password' => Hash::make('password123'),
                'role' => 'pimpinan',
                'unit_id' => Unit::first()?->id ?? 1,
                'nip' => '197003031990031003',
                'jabatan' => 'Kepala Sekolah',
                'is_active' => true,
            ]
        );

        // Kabag
        $units = Unit::take(3)->get();
        foreach ($units as $index => $unit) {
            User::firstOrCreate(
                ['email' => "kabag{$index}@siapsmk.local"],
                [
                    'name' => "Kepala Bagian " . ($index + 1),
                    'password' => Hash::make('password123'),
                    'role' => 'kabag',
                    'unit_id' => $unit->id,
                    'nip' => "19750{$index}0{$index}20000{$index}100" . ($index + 4),
                    'jabatan' => 'Kepala Bagian',
                    'is_active' => true,
                ]
            );
        }

        // Staff biasa
        for ($i = 1; $i <= 5; $i++) {
            User::firstOrCreate(
                ['email' => "staff{$i}@siapsmk.local"],
                [
                    'name' => "Staff User {$i}",
                    'password' => Hash::make('password123'),
                    'role' => 'staff',
                    'unit_id' => $units->random()->id,
                    'nip' => "19900{$i}0{$i}20150{$i}100" . ($i + 5),
                    'jabatan' => 'Staff',
                    'is_active' => true,
                ]
            );
        }
    }

    private function createSuratMasuk()
    {
        $units = Unit::all();
        $klasifikasis = KlasifikasiArsip::all();
        $sifatSurats = SifatSurat::all();
        
        $statuses = ['pending', 'diteruskan', 'didisposisi', 'selesai'];
        $users = User::all();

        for ($i = 1; $i <= 10; $i++) {
            SuratMasuk::create([
                'nomor_agenda' => sprintf('%04d', $i) . '/' . date('m') . '/' . date('Y'),
                'nomor_surat' => 'SM-' . sprintf('%03d', $i) . '/2025',
                'tanggal_surat' => now()->subDays(rand(1, 30)),
                'tanggal_terima' => now()->subDays(rand(0, 25)),
                'asal_surat' => 'Dinas Pendidikan Kota ' . ['Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Medan'][$i % 5],
                'perihal' => [
                    'Undangan Rapat Koordinasi',
                    'Permohonan Data Siswa',
                    'Nota Dinas Kegiatan',
                    'Surat Edaran Kebijakan Baru',
                    'Undangan Workshop',
                    'Laporan Bulanan',
                    'Permohonan Izin Kegiatan',
                    'Surat Tugas',
                    'Berita Acara Serah Terima',
                    'Surat Keterangan'
                ][$i - 1],
                'isi_ringkas' => "Ini adalah isi ringkas dari surat masuk nomor {$i}. Surat ini memerlukan tindak lanjut sesuai dengan prosedur yang berlaku.",
                'status' => $statuses[array_rand($statuses)],
                'sifat_id' => $sifatSurats->random()->id,
                'klasifikasi_id' => $klasifikasis->random()->id,
                'unit_id' => $units->random()->id,
                'user_id' => $users->where('role', 'staff_tu')->first()->id,
                'file_path' => null,
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }

        $this->command->info('✓ Created 10 surat masuk samples');
    }

    private function createSuratKeluar()
    {
        $units = Unit::all();
        $klasifikasis = KlasifikasiArsip::all();
        $sifatSurats = SifatSurat::all();
        $users = User::all();

        $statuses = ['draft', 'menunggu_review', 'menunggu_approve', 'disetujui', 'ditolak', 'finalized', 'signed', 'sent'];
        
        for ($i = 1; $i <= 5; $i++) {
            $status = $i <= 2 ? 'sent' : ($i == 3 ? 'signed' : ($i == 4 ? 'menunggu_approve' : 'draft'));
            
            SuratKeluar::create([
                'nomor_surat' => 'SK-' . sprintf('%03d', $i) . '/2025',
                'tanggal_surat' => now()->subDays(rand(1, 20)),
                'tujuan_surat' => 'Kepala Dinas Pendidikan Kota ' . ['Jakarta', 'Bandung', 'Surabaya', 'Semarang', 'Medan'][$i - 1],
                'perihal' => [
                    'Balasan Undangan Rapat',
                    'Pengiriman Data yang Diminta',
                    'Proposal Kegiatan Sekolah',
                    'Laporan Semester Ganjil',
                    'Permohonan Bantuan Fasilitas'
                ][$i - 1],
                'isi_ringkas' => "Ini adalah isi ringkas dari surat keluar nomor {$i}. Surat ini telah melalui proses review dan approval sesuai prosedur.",
                'status' => $status,
                'sifat_id' => $sifatSurats->random()->id,
                'klasifikasi_id' => $klasifikasis->random()->id,
                'unit_id' => $units->random()->id,
                'user_id' => $users->where('role', 'kabag')->first()?->id ?? $users->first()->id,
                'penandatangan' => $users->where('role', 'pimpinan')->first()?->name ?? 'Kepala Sekolah',
                'file_path' => null,
                'created_at' => now()->subDays(rand(1, 20)),
            ]);
        }

        $this->command->info('✓ Created 5 surat keluar samples');
    }

    private function createDisposisi()
    {
        $suratMasuks = SuratMasuk::all();
        $users = User::all();
        
        if ($suratMasuks->isEmpty()) {
            $this->command->warn('⚠ No surat masuk found. Skipping disposisi creation.');
            return;
        }

        $instruksi = [
            'Segera ditindaklanjuti',
            'Untuk diketahui',
            'Koordinasi dengan unit terkait',
            'Buat laporan hasil',
            'Hadiri rapat tersebut',
            'Verifikasi data terlebih dahulu',
            'Siapkan bahan presentasi',
            'Follow up minggu depan'
        ];

        // Create parent disposisi (level 1)
        $parentDisposisi = [];
        for ($i = 1; $i <= 5; $i++) {
            $surat = $suratMasuks->random();
            $pimpinan = $users->where('role', 'pimpinan')->first() ?? $users->first();
            
            $disposisi = Disposisi::create([
                'surat_masuk_id' => $surat->id,
                'dari_user_id' => $pimpinan->id,
                'ke_user_id' => $users->where('role', 'kabag')->first()?->id ?? $users->first()->id,
                'unit_id' => $surat->unit_id,
                'instruksi' => $instruksi[array_rand($instruksi)],
                'catatan' => 'Mohon segera ditindaklanjuti sesuai arahan.',
                'status' => 'pending',
                'parent_id' => null,
                'level' => 1,
                'created_at' => now()->subDays(rand(1, 15)),
            ]);
            
            $parentDisposisi[] = $disposisi;
        }

        // Create child disposisi (level 2)
        $childDisposisi = [];
        foreach ($parentDisposisi as $parent) {
            for ($j = 1; $j <= 2; $j++) {
                $disposisi = Disposisi::create([
                    'surat_masuk_id' => $parent->surat_masuk_id,
                    'dari_user_id' => $parent->ke_user_id,
                    'ke_user_id' => $users->where('role', 'staff')->random()->id,
                    'unit_id' => $parent->unit_id,
                    'instruksi' => $instruksi[array_rand($instruksi)],
                    'catatan' => 'Tindak lanjuti instruksi dari pimpinan.',
                    'status' => 'pending',
                    'parent_id' => $parent->id,
                    'level' => 2,
                    'created_at' => $parent->created_at->addHours(rand(1, 24)),
                ]);
                
                $childDisposisi[] = $disposisi;
            }
        }

        // Create grandchild disposisi (level 3)
        foreach ($childDisposisi->take(5) as $child) {
            Disposisi::create([
                'surat_masuk_id' => $child->surat_masuk_id,
                'dari_user_id' => $child->ke_user_id,
                'ke_user_id' => $users->where('role', 'staff')->random()->id,
                'unit_id' => $child->unit_id,
                'instruksi' => $instruksi[array_rand($instruksi)],
                'catatan' => 'Selesaikan sesuai deadline.',
                'status' => 'completed',
                'parent_id' => $child->id,
                'level' => 3,
                'created_at' => $child->created_at->addHours(rand(1, 12)),
            ]);
        }

        $this->command->info('✓ Created 15 disposisi with tree structure');
    }

    private function createDelegasi()
    {
        $users = User::all();
        $pimpinan = $users->where('role', 'pimpinan')->first() ?? $users->first();
        $kabag = $users->where('role', 'kabag')->first() ?? $users->skip(3)->first();
        $staffTu = $users->where('role', 'staff_tu')->first() ?? $users->skip(1)->first();

        // Delegasi 1 - Aktif (Pimpinan -> Kabag)
        Delegasi::create([
            'user_id' => $pimpinan->id,
            'pengganti_user_id' => $kabag->id,
            'tanggal_mulai' => now()->subDays(5),
            'tanggal_selesai' => now()->addDays(10),
            'is_active' => true,
        ]);

        // Delegasi 2 - Aktif (Kabag -> Staff)
        $staff = $users->where('role', 'staff')->first();
        Delegasi::create([
            'user_id' => $kabag->id,
            'pengganti_user_id' => $staff->id,
            'tanggal_mulai' => now()->subDays(2),
            'tanggal_selesai' => now()->addDays(5),
            'is_active' => true,
        ]);

        // Delegasi 3 - Nonaktif
        Delegasi::create([
            'user_id' => $staffTu->id,
            'pengganti_user_id' => $users->where('role', 'staff')->skip(1)->first()->id,
            'tanggal_mulai' => now()->subDays(30),
            'tanggal_selesai' => now()->subDays(10),
            'is_active' => false,
        ]);

        $this->command->info('✓ Created 3 delegasi (2 active, 1 inactive)');
    }
}
