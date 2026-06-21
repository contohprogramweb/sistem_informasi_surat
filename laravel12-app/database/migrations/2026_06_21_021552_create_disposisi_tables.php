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
        Schema::create('disposisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_masuk_id')->constrained()->onDelete('cascade');
            $table->foreignId('dari_user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ke_user_id')->constrained('users')->onDelete('cascade');
            $table->text('instruksi');
            $table->date('batas_waktu')->nullable();
            $table->enum('prioritas', ['Rendah', 'Normal', 'Tinggi', 'Segera'])->default('Normal');
            $table->enum('status', ['Belum Dibaca', 'Sudah Dibaca', 'Sedang Ditindaklanjuti', 'Selesai', 'Belum Selesai'])->default('Belum Dibaca');
            $table->foreignId('parent_id')->nullable()->constrained('disposisi')->onDelete('cascade');
            $table->json('tembusan')->nullable(); // Array user IDs
            $table->timestamp('read_at')->nullable(); // Read receipt timestamp
            $table->text('komentar_selesai')->nullable();
            $table->string('file_tindak_lanjut')->nullable(); // Path file upload saat selesai
            $table->timestamps();
            
            $table->index(['surat_masuk_id', 'status']);
            $table->index(['ke_user_id', 'status']);
            $table->index(['parent_id']);
        });

        Schema::create('delegasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('pengganti_user_id')->constrained('users')->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'is_active']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegasi');
        Schema::dropIfExists('disposisi');
    }
};
