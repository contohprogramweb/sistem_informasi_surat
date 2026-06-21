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
            $table->string('nomor_surat')->nullable()->unique();
            $table->date('tanggal_surat');
            $table->string('tujuan');
            $table->string('jabatan_tujuan')->nullable();
            $table->string('perihal');
            $table->foreignId('klasifikasi_id')->constrained('klasifikasi_arsip')->onDelete('restrict');
            $table->enum('status', ['draft', 'review', 'disetujui', 'siap_ttd', 'tertandatangani', 'terkirim'])->default('draft');
            $table->enum('sifat', ['biasa', 'penting', 'rahasia'])->default('biasa');
            $table->text('isi_ringkas')->nullable();
            $table->foreignId('unit_id')->constrained('units')->onDelete('restrict');
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['nomor_surat', 'tanggal_surat', 'status']);
            $table->index(['unit_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_keluar');
    }
};
