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
        Schema::create('surat_masuk', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_agenda')->unique();
            $table->string('nomor_surat');
            $table->date('tanggal_surat');
            $table->date('tanggal_terima')->default(now());
            $table->string('pengirim');
            $table->string('jabatan_pengirim')->nullable();
            $table->string('perihal');
            $table->foreignId('klasifikasi_id')->constrained('klasifikasi_arsip')->onDelete('restrict');
            $table->enum('sifat', ['biasa', 'penting', 'rahasia'])->default('biasa');
            $table->enum('status', ['baru', 'dibaca', 'didisposisi', 'selesai'])->default('baru');
            $table->enum('prioritas', ['rendah', 'sedang', 'tinggi', 'urgens'])->default('sedang');
            $table->text('isi_ringkas')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->boolean('requires_disposition')->default(false);
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['nomor_surat', 'tanggal_surat', 'status']);
            $table->index(['klasifikasi_id', 'sifat']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_masuk');
    }
};
