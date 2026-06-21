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
        // Tabel counter untuk nomor agenda otomatis
        Schema::create('agenda_counters', function (Blueprint $table) {
            $table->id();
            $table->string('unit_code', 10)->unique();
            $table->year('year');
            $table->integer('last_number')->default(0);
            $table->timestamps();
            
            $table->index(['unit_code', 'year']);
        });

        // Update tabel surat_masuk yang sudah ada
        Schema::table('surat_masuk', function (Blueprint $table) {
            // Kolom existing yang perlu dipastikan ada
            if (!Schema::hasColumn('surat_masuk', 'agenda')) {
                $table->string('agenda', 50)->nullable()->after('id');
            }
            if (!Schema::hasColumn('surat_masuk', 'tanggal_terima')) {
                $table->date('tanggal_terima')->after('agenda');
            }
            if (!Schema::hasColumn('surat_masuk', 'cara_terima')) {
                $table->enum('cara_terima', ['datang_langsung', 'pos', 'kurir', 'email'])->after('tanggal_terima');
            }
            if (!Schema::hasColumn('surat_masuk', 'penerima_fisik')) {
                $table->string('penerima_fisik')->nullable()->after('cara_terima');
            }
            if (!Schema::hasColumn('surat_masuk', 'nomor_surat')) {
                $table->string('nomor_surat')->after('penerima_fisik');
            }
            if (!Schema::hasColumn('surat_masuk', 'tanggal_surat')) {
                $table->date('tanggal_surat')->after('nomor_surat');
            }
            if (!Schema::hasColumn('surat_masuk', 'pengirim')) {
                $table->string('pengirim')->after('tanggal_surat');
            }
            if (!Schema::hasColumn('surat_masuk', 'perihal')) {
                $table->text('perihal')->after('pengirim');
            }
            if (!Schema::hasColumn('surat_masuk', 'ringkasan')) {
                $table->text('ringkasan')->nullable()->after('perihal');
            }
            if (!Schema::hasColumn('surat_masuk', 'klasifikasi_id')) {
                $table->foreignId('klasifikasi_id')->nullable()->constrained('klasifikasi_arsip')->onDelete('set null')->after('ringkasan');
            }
            if (!Schema::hasColumn('surat_masuk', 'sifat_id')) {
                $table->foreignId('sifat_id')->nullable()->constrained('sifat_surats')->onDelete('set null')->after('klasifikasi_id');
            }
            if (!Schema::hasColumn('surat_masuk', 'prioritas')) {
                $table->enum('prioritas', ['Rendah', 'Normal', 'Tinggi', 'Segera'])->default('Normal')->after('sifat_id');
            }
            if (!Schema::hasColumn('surat_masuk', 'indeks')) {
                $table->json('indeks')->nullable()->after('prioritas');
            }
            if (!Schema::hasColumn('surat_masuk', 'tidak_perlu_disposisi')) {
                $table->boolean('tidak_perlu_disposisi')->default(false)->after('indeks');
            }
            if (!Schema::hasColumn('surat_masuk', 'status')) {
                $table->enum('status', ['Aktif', 'Diarsipkan', 'Dihapus'])->default('Aktif')->after('tidak_perlu_disposisi');
            }
            if (!Schema::hasColumn('surat_masuk', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('surat_masuk', 'deleted_until')) {
                $table->timestamp('deleted_until')->nullable()->after('deleted_at');
            }
        });

        // Tabel pivot untuk unit tujuan (multi-select)
        Schema::create('surat_masuk_unit_tujuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('surat_masuk_id')->constrained('surat_masuk')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['surat_masuk_id', 'unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_masuk_unit_tujuan');
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropForeign(['klasifikasi_id']);
            $table->dropForeign(['sifat_id']);
            $table->dropColumn([
                'agenda', 'tanggal_terima', 'cara_terima', 'penerima_fisik',
                'nomor_surat', 'tanggal_surat', 'pengirim', 'perihal',
                'ringkasan', 'klasifikasi_id', 'sifat_id', 'prioritas',
                'indeks', 'tidak_perlu_disposisi', 'status', 'deleted_at', 'deleted_until'
            ]);
        });
        Schema::dropIfExists('agenda_counters');
    }
};
