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
        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->date('tanggal_arsip')->nullable()->after('status');
            $table->date('tanggal_jatuh_aktif')->nullable()->after('tanggal_arsip');
            $table->date('tanggal_jatuh_inaktif')->nullable()->after('tanggal_jatuh_aktif');
            $table->enum('status_arsip', ['aktif', 'inaktif', 'dimusnahkan'])->default('aktif')->after('status');
            $table->text('alasan_hapus')->nullable()->after('deleted_until');
            $table->timestamp('dimusnahkan_at')->nullable()->after('status_arsip');
            $table->foreignId('dimusnahkan_by')->nullable()->constrained('users')->after('dimusnahkan_at');
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->date('tanggal_arsip')->nullable()->after('status');
            $table->date('tanggal_jatuh_aktif')->nullable()->after('tanggal_arsip');
            $table->date('tanggal_jatuh_inaktif')->nullable()->after('tanggal_jatuh_aktif');
            $table->enum('status_arsip', ['aktif', 'inaktif', 'dimusnahkan'])->default('aktif')->after('status');
            $table->text('alasan_hapus')->nullable()->after('deleted_until');
            $table->timestamp('dimusnahkan_at')->nullable()->after('status_arsip');
            $table->foreignId('dimusnahkan_by')->nullable()->constrained('users')->after('dimusnahkan_at');
        });

        Schema::create('berita_acara_pemusnahan', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_berita_acara')->unique();
            $table->date('tanggal_berita_acara');
            $table->text('keterangan')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('berita_acara_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('berita_acara_id')->constrained('berita_acara_pemusnahan')->onDelete('cascade');
            $table->morphs('arsip'); // surat_masuk_id/surat_masuk_type atau surat_keluar_id/surat_keluar_type
            $table->string('nomor_surat');
            $table->date('tanggal_surat');
            $table->string('perihal');
            $table->integer('retensi_aktif_tahun');
            $table->integer('retensi_inaktif_tahun');
            $table->date('tanggal_jatuh_tempo');
            $table->timestamps();
        });

        Schema::create('arsip_notifications', function (Blueprint $table) {
            $table->id();
            $table->morphs('arsip'); // surat_masuk_id/surat_masuk_type atau surat_keluar_id/surat_keluar_type
            $table->string('type'); // 'jatuh_tempo_aktif', 'jatuh_tempo_inaktif', 'reminder_pemusnahan'
            $table->integer('bulan_sebelumnya');
            $table->boolean('is_read')->default(false);
            $table->timestamp('sent_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip_notifications');
        Schema::dropIfExists('berita_acara_detail');
        Schema::dropIfExists('berita_acara_pemusnahan');

        Schema::table('surat_masuk', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_arsip',
                'tanggal_jatuh_aktif',
                'tanggal_jatuh_inaktif',
                'status_arsip',
                'alasan_hapus',
                'dimusnahkan_at',
                'dimusnahkan_by'
            ]);
        });

        Schema::table('surat_keluar', function (Blueprint $table) {
            $table->dropColumn([
                'tanggal_arsip',
                'tanggal_jatuh_aktif',
                'tanggal_jatuh_inaktif',
                'status_arsip',
                'alasan_hapus',
                'dimusnahkan_at',
                'dimusnahkan_by'
            ]);
        });
    }
};
