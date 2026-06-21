<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Definisi semua permissions sesuai spesifikasi
        $permissions = [
            // Surat Masuk
            'surat_masuk.view.all',
            'surat_masuk.view.unit',
            'surat_masuk.create',
            'surat_masuk.edit',
            
            // Surat Keluar
            'surat_keluar.create',
            'surat_keluar.review',
            'surat_keluar.approve',
            'surat_keluar.ttd',
            
            // Disposisi
            'disposisi.create',
            'disposisi.receive',
            'disposisi.forward',
            'disposisi.massal',
            
            // Master Data & Admin
            'master_data.manage',
            'user.manage',
            'laporan.view',
            'arsip.manage',
            
            // Fitur Lanjutan
            'tte.execute',
            'import.execute',
            'audit.view',
        ];

        // Buat semua permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Buat roles
        $pimpinan = Role::firstOrCreate(['name' => 'pimpinan']);
        $kabag = Role::firstOrCreate(['name' => 'kabag']);
        $staff_tu = Role::firstOrCreate(['name' => 'staff_tu']);

        // Assign permissions ke role Pimpinan
        $pimpinan->givePermissionTo([
            'surat_masuk.view.all',
            'surat_masuk.create',
            'surat_masuk.edit',
            'surat_keluar.create',
            'surat_keluar.review',
            'surat_keluar.approve',
            'surat_keluar.ttd',
            'disposisi.create',
            'disposisi.receive',
            'disposisi.forward',
            'disposisi.massal',
            'master_data.manage',
            'user.manage',
            'laporan.view',
            'arsip.manage',
            'tte.execute',
            'import.execute',
            'audit.view',
        ]);

        // Assign permissions ke role Kabag
        $kabag->givePermissionTo([
            'surat_masuk.view.unit',
            'surat_masuk.create',
            'surat_masuk.edit',
            'surat_keluar.create',
            'surat_keluar.review',
            'disposisi.create',
            'disposisi.receive',
            'disposisi.forward',
            'laporan.view',
            'arsip.manage',
        ]);

        // Assign permissions ke role Staff TU
        $staff_tu->givePermissionTo([
            'surat_masuk.view.all',
            'surat_masuk.create',
            'surat_masuk.edit',
            'surat_keluar.create',
            'disposisi.create',
            'disposisi.receive',
            'disposisi.forward',
            'import.execute',
        ]);

        $this->command->info('Roles dan Permissions berhasil dibuat!');
        $this->command->info('Roles: pimpinan, kabag, staff_tu');
        $this->command->info('Total Permissions: ' . count($permissions));
    }
}
