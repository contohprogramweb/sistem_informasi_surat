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
        Schema::create('tte_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('surat_keluar_id')->constrained('surat_keluar')->onDelete('cascade');
            $table->string('hash_file', 64); // SHA-256 hash
            $table->string('pdf_path');
            $table->integer('position_x')->nullable();
            $table->integer('position_y')->nullable();
            $table->decimal('scale', 5, 2)->default(1.00);
            $table->string('ip_address', 45)->nullable();
            $table->text('error_message')->nullable();
            $table->boolean('success')->default(true);
            $table->timestamps();

            $table->index(['surat_keluar_id', 'user_id']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tte_logs');
    }
};
