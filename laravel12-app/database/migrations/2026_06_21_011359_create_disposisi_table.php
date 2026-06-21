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
            $table->foreignId('surat_id')->constrained('surat_masuk')->onDelete('cascade');
            $table->foreignId('dari_user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('ke_user_id')->constrained('users')->onDelete('restrict');
            $table->text('instruksi');
            $table->date('batas_waktu')->nullable();
            $table->enum('status', ['pending', 'diproses', 'selesai'])->default('pending');
            $table->foreignId('parent_id')->nullable()->constrained('disposisi')->onDelete('cascade');
            $table->text('catatan')->nullable();
            $table->timestamps();
            
            $table->index(['surat_id', 'status']);
            $table->index(['ke_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposisi');
    }
};
