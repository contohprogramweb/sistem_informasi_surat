<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only for MySQL database
        if (config('database.default') !== 'mysql') {
            return;
        }

        try {
            // Add FULLTEXT index to surat_masuk table
            DB::statement("ALTER TABLE surat_masuk ADD FULLTEXT INDEX ft_surat_masuk_search (agenda, nomor_surat, perihal, pengirim, indeks)");
            
            // Add FULLTEXT index to surat_keluar table
            DB::statement("ALTER TABLE surat_keluar ADD FULLTEXT INDEX ft_surat_keluar_search (nomor_surat_final, perihal, tujuan, isi_ringkas)");
            
            \Log::info('FULLTEXT indexes created successfully for search functionality');
        } catch (\Exception $e) {
            // Indexes might already exist or engine doesn't support FULLTEXT
            \Log::warning('FULLTEXT index creation skipped: ' . $e->getMessage());
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (config('database.default') !== 'mysql') {
            return;
        }

        try {
            // Drop FULLTEXT indexes
            DB::statement("ALTER TABLE surat_masuk DROP INDEX IF EXISTS ft_surat_masuk_search");
            DB::statement("ALTER TABLE surat_keluar DROP INDEX IF EXISTS ft_surat_keluar_search");
            
            \Log::info('FULLTEXT indexes dropped successfully');
        } catch (\Exception $e) {
            \Log::warning('FULLTEXT index drop skipped: ' . $e->getMessage());
        }
    }
};
