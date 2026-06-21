<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel Audit Logs (Append-only)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); // created, updated, deleted
            $table->string('entity'); // Model name: App\Models\SuratMasuk
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
            
            $table->index(['entity', 'entity_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('created_at'); // Untuk scheduler retention
        });

        // Tabel Import Batches (Untuk tracking import massal)
        Schema::create('import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('type'); // surat_masuk, surat_keluar, users, etc
            $table->string('filename');
            $table->integer('total_rows');
            $table->integer('success_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->json('errors')->nullable(); // Store row-level errors
            $table->enum('status', ['processing', 'completed', 'failed'])->default('processing');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_batches');
        Schema::dropIfExists('audit_logs');
    }
};
