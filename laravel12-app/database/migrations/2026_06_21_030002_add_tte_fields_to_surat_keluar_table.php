<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            // Tambah kolom untuk tracking TTE jika belum ada
            if (!Schema::hasColumn('surat_keluar', 'signed_by')) {
                $table->foreignId('signed_by')->nullable()->after('approver_id')->constrained('users')->onDelete('set null');
            }
            
            // Kolom untuk hash file final setelah TTE
            if (!Schema::hasColumn('surat_keluar', 'hash_file_final')) {
                $table->string('hash_file_final', 64)->nullable()->after('resi');
            }
            
            // Kolom untuk path PDF final
            if (!Schema::hasColumn('surat_keluar', 'pdf_final_path')) {
                $table->string('pdf_final_path')->nullable()->after('hash_file_final');
            }
            
            // Timestamp saat TTE dilakukan
            if (!Schema::hasColumn('surat_keluar', 'signed_at')) {
                $table->timestamp('signed_at')->nullable()->after('signed_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropForeign(['signed_by']);
            $table->dropColumn(['signed_by', 'hash_file_final', 'pdf_final_path', 'signed_at']);
        });
    }
};
