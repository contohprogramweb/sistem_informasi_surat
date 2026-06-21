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
        Schema::create('klasifikasi_arsip', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('klasifikasi_arsip')->onDelete('cascade');
            $table->string('kode')->unique();
            $table->string('nama');
            $table->text('deskripsi')->nullable();
            $table->integer('level')->default(1);
            $table->integer('retensi_aktif')->default(0)->comment('Tahun retensi aktif');
            $table->integer('retensi_inaktif')->default(0)->comment('Tahun retensi inaktif');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['parent_id', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('klasifikasi_arsip');
    }
};
