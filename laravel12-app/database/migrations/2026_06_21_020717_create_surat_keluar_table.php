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
        Schema::create('surat_keluar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_pembuat_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('klasifikasi_id')->constrained('klasifikasi_arsip')->onDelete('set null');
            $table->foreignId('sifat_id')->constrained('sifat_surats')->onDelete('set null');
            
            // Informasi Surat
            $table->string('tujuan');
            $table->string('perihal');
            $table->text('isi_ringkas');
            $table->string('nomor_surat_final')->nullable();
            $table->date('tanggal_surat_final')->nullable();
            
            // Workflow Status
            $table->string('status')->default(\App\Enums\SuratKeluarStatus::Draft->value);
            $table->text('catatan_review')->nullable();
            $table->text('alasan_tolak')->nullable();
            
            // Pengiriman
            $table->string('cara_kirim')->nullable();
            $table->date('tanggal_kirim')->nullable();
            $table->string('resi')->nullable();
            
            // Users yang terlibat
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('signed_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_at']);
            $table->index(['unit_pembuat_id', 'status']);
        });

        // Tabel untuk melacak history transisi status
        Schema::create('surat_keluar_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_keluar_id')->constrained('surat_keluar')->onDelete('cascade');
            $table->string('from_status');
            $table->string('to_status');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('surat_keluar_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keluar_histories');
        Schema::dropIfExists('surat_keluar');
    }
};
