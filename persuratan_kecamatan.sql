-- ============================================
-- SISTEM INFORMASI PERSURATAN KANTOR KECAMATAN
-- Skema Basis Data Lengkap & Data Dummy
-- ============================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================
-- 1. TABEL UTAMA (CORE TABLES)
-- ============================================

-- Tabel Users
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nip` varchar(50) DEFAULT NULL UNIQUE,
  `jabatan` varchar(100) DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_unit_id_foreign` (`unit_id`),
  KEY `users_is_active_index` (`unit_id`, `is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Units (Unit Kerja)
CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode_unit` varchar(50) NOT NULL UNIQUE,
  `nama_unit` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `units_kode_unit_unique` (`kode_unit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Klasifikasi Arsip
CREATE TABLE `klasifikasi_arsip` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kode` varchar(50) NOT NULL UNIQUE,
  `nama` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `level` int(11) NOT NULL DEFAULT 1,
  `retensi_aktif` int(11) NOT NULL DEFAULT 0 COMMENT 'Tahun retensi aktif',
  `retensi_inaktif` int(11) NOT NULL DEFAULT 0 COMMENT 'Tahun retensi inaktif',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `klasifikasi_arsip_kode_unique` (`kode`),
  KEY `klasifikasi_arsip_parent_id_foreign` (`parent_id`),
  KEY `parent_id_level_index` (`parent_id`, `level`),
  CONSTRAINT `klasifikasi_arsip_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `klasifikasi_arsip` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Sifat Surat
CREATE TABLE `sifat_surats` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` varchar(50) NOT NULL UNIQUE,
  `keterangan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sifat_surats_nama_unique` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. TABEL SURAT MASUK
-- ============================================

CREATE TABLE `surat_masuk` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_agenda` varchar(50) NOT NULL UNIQUE,
  `agenda` varchar(50) DEFAULT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `tanggal_terima` date NOT NULL DEFAULT CURRENT_DATE,
  `tanggal_arsip` date DEFAULT NULL,
  `tanggal_jatuh_aktif` date DEFAULT NULL,
  `tanggal_jatuh_inaktif` date DEFAULT NULL,
  `pengirim` varchar(255) NOT NULL,
  `jabatan_pengirim` varchar(100) DEFAULT NULL,
  `perihal` text NOT NULL,
  `klasifikasi_id` bigint(20) UNSIGNED NOT NULL,
  `sifat_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sifat` enum('biasa','penting','rahasia') NOT NULL DEFAULT 'biasa',
  `status` enum('baru','dibaca','didisposisi','selesai','Aktif','Diarsipkan','Dihapus') NOT NULL DEFAULT 'baru',
  `status_arsip` enum('aktif','inaktif','dimusnahkan') NOT NULL DEFAULT 'aktif',
  `prioritas` enum('rendah','sedang','tinggi','urgens','Rendah','Normal','Tinggi','Segera') NOT NULL DEFAULT 'sedang',
  `isi_ringkas` text DEFAULT NULL,
  `ringkasan` text DEFAULT NULL,
  `indeks` json DEFAULT NULL,
  `cara_terima` enum('datang_langsung','pos','kurir','email') DEFAULT NULL,
  `penerima_fisik` varchar(100) DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `requires_disposition` tinyint(1) NOT NULL DEFAULT 0,
  `tidak_perlu_disposisi` tinyint(1) NOT NULL DEFAULT 0,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `deleted_until` timestamp NULL DEFAULT NULL,
  `alasan_hapus` text DEFAULT NULL,
  `dimusnahkan_at` timestamp NULL DEFAULT NULL,
  `dimusnahkan_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_masuk_nomor_agenda_unique` (`nomor_agenda`),
  KEY `surat_masuk_klasifikasi_id_foreign` (`klasifikasi_id`),
  KEY `surat_masuk_sifat_id_foreign` (`sifat_id`),
  KEY `surat_masuk_unit_id_foreign` (`unit_id`),
  KEY `surat_masuk_dimusnahkan_by_foreign` (`dimusnahkan_by`),
  KEY `nomor_surat_index` (`nomor_surat`, `tanggal_surat`, `status`),
  KEY `klasifikasi_sifat_index` (`klasifikasi_id`, `sifat`),
  CONSTRAINT `surat_masuk_klasifikasi_id_foreign` FOREIGN KEY (`klasifikasi_id`) REFERENCES `klasifikasi_arsip` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `surat_masuk_sifat_id_foreign` FOREIGN KEY (`sifat_id`) REFERENCES `sifat_surats` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_masuk_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_masuk_dimusnahkan_by_foreign` FOREIGN KEY (`dimusnahkan_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Pivot Unit Tujuan Surat Masuk
CREATE TABLE `surat_masuk_unit_tujuan` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `surat_masuk_id` bigint(20) UNSIGNED NOT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_masuk_unit_tujuan_unique` (`surat_masuk_id`, `unit_id`),
  KEY `surat_masuk_unit_tujuan_unit_id_foreign` (`unit_id`),
  CONSTRAINT `surat_masuk_unit_tujuan_surat_masuk_id_foreign` FOREIGN KEY (`surat_masuk_id`) REFERENCES `surat_masuk` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_masuk_unit_tujuan_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. TABEL SURAT KELUAR
-- ============================================

CREATE TABLE `surat_keluar` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_surat` varchar(100) DEFAULT NULL UNIQUE,
  `nomor_surat_final` varchar(100) DEFAULT NULL,
  `tanggal_surat` date NOT NULL,
  `tanggal_surat_final` date DEFAULT NULL,
  `tujuan` varchar(255) NOT NULL,
  `jabatan_tujuan` varchar(100) DEFAULT NULL,
  `perihal` varchar(255) NOT NULL,
  `klasifikasi_id` bigint(20) UNSIGNED NOT NULL,
  `sifat_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'draft',
  `status_arsip` enum('aktif','inaktif','dimusnahkan') NOT NULL DEFAULT 'aktif',
  `sifat` enum('biasa','penting','rahasia') NOT NULL DEFAULT 'biasa',
  `isi_ringkas` text DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED NOT NULL,
  `unit_pembuat_id` bigint(20) UNSIGNED NOT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `reviewer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `signed_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `signed_at` timestamp NULL DEFAULT NULL,
  `catatan_review` text DEFAULT NULL,
  `alasan_tolak` text DEFAULT NULL,
  `cara_kirim` varchar(50) DEFAULT NULL,
  `tanggal_kirim` date DEFAULT NULL,
  `resi` varchar(100) DEFAULT NULL,
  `hash_file_final` varchar(64) DEFAULT NULL,
  `pdf_final_path` varchar(255) DEFAULT NULL,
  `tanggal_arsip` date DEFAULT NULL,
  `tanggal_jatuh_aktif` date DEFAULT NULL,
  `tanggal_jatuh_inaktif` date DEFAULT NULL,
  `alasan_hapus` text DEFAULT NULL,
  `dimusnahkan_at` timestamp NULL DEFAULT NULL,
  `dimusnahkan_by` bigint(20) UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surat_keluar_nomor_surat_unique` (`nomor_surat`),
  KEY `surat_keluar_klasifikasi_id_foreign` (`klasifikasi_id`),
  KEY `surat_keluar_sifat_id_foreign` (`sifat_id`),
  KEY `surat_keluar_unit_id_foreign` (`unit_id`),
  KEY `surat_keluar_unit_pembuat_id_foreign` (`unit_pembuat_id`),
  KEY `surat_keluar_created_by_foreign` (`created_by`),
  KEY `surat_keluar_approved_by_foreign` (`approved_by`),
  KEY `surat_keluar_reviewer_id_foreign` (`reviewer_id`),
  KEY `surat_keluar_signed_by_foreign` (`signed_by`),
  KEY `surat_keluar_dimusnahkan_by_foreign` (`dimusnahkan_by`),
  KEY `nomor_surat_status_index` (`nomor_surat`, `tanggal_surat`, `status`),
  KEY `unit_status_index` (`unit_id`, `status`),
  KEY `status_created_at_index` (`status`, `created_at`),
  KEY `unit_pembuat_status_index` (`unit_pembuat_id`, `status`),
  CONSTRAINT `surat_keluar_klasifikasi_id_foreign` FOREIGN KEY (`klasifikasi_id`) REFERENCES `klasifikasi_arsip` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `surat_keluar_sifat_id_foreign` FOREIGN KEY (`sifat_id`) REFERENCES `sifat_surats` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_keluar_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `surat_keluar_unit_pembuat_id_foreign` FOREIGN KEY (`unit_pembuat_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_keluar_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `surat_keluar_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_keluar_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_keluar_signed_by_foreign` FOREIGN KEY (`signed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `surat_keluar_dimusnahkan_by_foreign` FOREIGN KEY (`dimusnahkan_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel History Surat Keluar
CREATE TABLE `surat_keluar_histories` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `surat_keluar_id` bigint(20) UNSIGNED NOT NULL,
  `from_status` varchar(50) NOT NULL,
  `to_status` varchar(50) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `surat_keluar_histories_surat_keluar_id_foreign` (`surat_keluar_id`),
  KEY `surat_keluar_histories_user_id_foreign` (`user_id`),
  KEY `surat_keluar_id_index` (`surat_keluar_id`),
  CONSTRAINT `surat_keluar_histories_surat_keluar_id_foreign` FOREIGN KEY (`surat_keluar_id`) REFERENCES `surat_keluar` (`id`) ON DELETE CASCADE,
  CONSTRAINT `surat_keluar_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. TABEL DISPOSISI
-- ============================================

CREATE TABLE `disposisi` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `surat_id` bigint(20) UNSIGNED DEFAULT NULL,
  `surat_masuk_id` bigint(20) UNSIGNED DEFAULT NULL,
  `dari_user_id` bigint(20) UNSIGNED NOT NULL,
  `ke_user_id` bigint(20) UNSIGNED NOT NULL,
  `instruksi` text NOT NULL,
  `batas_waktu` date DEFAULT NULL,
  `prioritas` enum('pending','diproses','selesai','Belum Dibaca','Sudah Dibaca','Sedang Ditindaklanjuti','Selesai','Belum Selesai','Rendah','Normal','Tinggi','Segera') NOT NULL DEFAULT 'pending',
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `tembusan` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `is_read_first` tinyint(1) NOT NULL DEFAULT 0,
  `first_read_at` timestamp NULL DEFAULT NULL,
  `komentar_selesai` text DEFAULT NULL,
  `file_tindak_lanjut` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `disposisi_surat_id_foreign` (`surat_id`),
  KEY `disposisi_surat_masuk_id_foreign` (`surat_masuk_id`),
  KEY `disposisi_dari_user_id_foreign` (`dari_user_id`),
  KEY `disposisi_ke_user_id_foreign` (`ke_user_id`),
  KEY `disposisi_parent_id_foreign` (`parent_id`),
  KEY `surat_id_status_index` (`surat_id`, `status`),
  KEY `ke_user_id_status_index` (`ke_user_id`, `status`),
  KEY `parent_id_index` (`parent_id`),
  KEY `surat_masuk_id_status_index` (`surat_masuk_id`, `status`),
  CONSTRAINT `disposisi_surat_id_foreign` FOREIGN KEY (`surat_id`) REFERENCES `surat_masuk` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disposisi_surat_masuk_id_foreign` FOREIGN KEY (`surat_masuk_id`) REFERENCES `surat_masuk` (`id`) ON DELETE CASCADE,
  CONSTRAINT `disposisi_dari_user_id_foreign` FOREIGN KEY (`dari_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `disposisi_ke_user_id_foreign` FOREIGN KEY (`ke_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `disposisi_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `disposisi` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. TABEL LAMPIRAN
-- ============================================

CREATE TABLE `lampiran` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `attachable_type` varchar(255) NOT NULL,
  `attachable_id` bigint(20) UNSIGNED NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `hash` varchar(64) DEFAULT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lampiran_attachable_type_attachable_id_index` (`attachable_type`, `attachable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. TABEL DELEGASI
-- ============================================

CREATE TABLE `delegasi` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `pengganti_user_id` bigint(20) UNSIGNED NOT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `delegasi_user_id_foreign` (`user_id`),
  KEY `delegasi_pengganti_user_id_foreign` (`pengganti_user_id`),
  KEY `user_id_is_active_index` (`user_id`, `is_active`),
  KEY `tanggal_mulai_selesai_index` (`tanggal_mulai`, `tanggal_selesai`),
  CONSTRAINT `delegasi_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `delegasi_pengganti_user_id_foreign` FOREIGN KEY (`pengganti_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. TABEL TEMPLATE DISPOSISI
-- ============================================

CREATE TABLE `template_disposisi` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `instruksi_default` text NOT NULL,
  `tujuan_default` json DEFAULT NULL,
  `tembusan_default` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `template_disposisi_user_id_foreign` (`user_id`),
  KEY `user_id_is_active_index` (`user_id`, `is_active`),
  KEY `user_id_index` (`user_id`),
  CONSTRAINT `template_disposisi_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. TABEL CATATAN PRIBADI
-- ============================================

CREATE TABLE `catatan_pribadi` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `surat_type` varchar(255) NOT NULL,
  `surat_id` bigint(20) UNSIGNED NOT NULL,
  `isi` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `catatan_pribadi_user_id_foreign` (`user_id`),
  KEY `user_id_surat_type_surat_id_index` (`user_id`, `surat_type`, `surat_id`),
  CONSTRAINT `catatan_pribadi_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. TABEL AUDIT LOGS
-- ============================================

CREATE TABLE `audit_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity` varchar(255) NOT NULL,
  `entity_id` bigint(20) UNSIGNED NOT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `user_id_action_index` (`user_id`, `action`),
  KEY `entity_entity_id_index` (`entity`, `entity_id`),
  KEY `created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 10. TABEL ARSIP & RETENSI
-- ============================================

CREATE TABLE `berita_acara_pemusnahan` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_berita_acara` varchar(50) NOT NULL UNIQUE,
  `tanggal_berita_acara` date NOT NULL,
  `keterangan` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `berita_acara_pemusnahan_nomor_berita_acara_unique` (`nomor_berita_acara`),
  KEY `berita_acara_pemusnahan_created_by_foreign` (`created_by`),
  KEY `berita_acara_pemusnahan_approved_by_foreign` (`approved_by`),
  CONSTRAINT `berita_acara_pemusnahan_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `berita_acara_pemusnahan_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `berita_acara_detail` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `berita_acara_id` bigint(20) UNSIGNED NOT NULL,
  `arsip_type` varchar(255) NOT NULL,
  `arsip_id` bigint(20) UNSIGNED NOT NULL,
  `nomor_surat` varchar(100) NOT NULL,
  `tanggal_surat` date NOT NULL,
  `perihal` varchar(255) NOT NULL,
  `retensi_aktif_tahun` int(11) NOT NULL,
  `retensi_inaktif_tahun` int(11) NOT NULL,
  `tanggal_jatuh_tempo` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `berita_acara_detail_berita_acara_id_foreign` (`berita_acara_id`),
  KEY `arsip_type_arsip_id_index` (`arsip_type`, `arsip_id`),
  CONSTRAINT `berita_acara_detail_berita_acara_id_foreign` FOREIGN KEY (`berita_acara_id`) REFERENCES `berita_acara_pemusnahan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `arsip_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `arsip_type` varchar(255) NOT NULL,
  `arsip_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `bulan_sebelumnya` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `sent_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `arsip_notifications_arsip_type_arsip_id_index` (`arsip_type`, `arsip_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 11. TABEL TTE (TANDA TANGAN ELEKTRONIK)
-- ============================================

CREATE TABLE `tte_signatures` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `encrypted_path` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL DEFAULT 'image/png',
  `file_size` bigint(20) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tte_signatures_user_id_foreign` (`user_id`),
  KEY `user_id_is_active_index` (`user_id`, `is_active`),
  CONSTRAINT `tte_signatures_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `tte_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `surat_keluar_id` bigint(20) UNSIGNED NOT NULL,
  `hash_file` varchar(64) NOT NULL,
  `pdf_path` varchar(255) NOT NULL,
  `position_x` int(11) DEFAULT NULL,
  `position_y` int(11) DEFAULT NULL,
  `scale` decimal(5,2) NOT NULL DEFAULT 1.00,
  `ip_address` varchar(45) DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `success` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tte_logs_user_id_foreign` (`user_id`),
  KEY `tte_logs_surat_keluar_id_foreign` (`surat_keluar_id`),
  KEY `surat_keluar_id_user_id_index` (`surat_keluar_id`, `user_id`),
  KEY `created_at_index` (`created_at`),
  CONSTRAINT `tte_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tte_logs_surat_keluar_id_foreign` FOREIGN KEY (`surat_keluar_id`) REFERENCES `surat_keluar` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 12. TABEL NOTIFIKASI & PREFERENSI
-- ============================================

CREATE TABLE `notification_preferences` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL UNIQUE,
  `email_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `frequency` varchar(50) NOT NULL DEFAULT 'immediate',
  `types` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `notification_preferences_user_id_unique` (`user_id`),
  CONSTRAINT `notification_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 13. TABEL AGENDA COUNTERS
-- ============================================

CREATE TABLE `agenda_counters` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `unit_code` varchar(10) NOT NULL UNIQUE,
  `year` year(4) NOT NULL,
  `last_number` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agenda_counters_unit_code_unique` (`unit_code`),
  KEY `unit_code_year_index` (`unit_code`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 14. TABEL IMPORT BATCHES
-- ============================================

CREATE TABLE `import_batches` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(50) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `total_rows` int(11) NOT NULL,
  `success_count` int(11) NOT NULL DEFAULT 0,
  `failed_count` int(11) NOT NULL DEFAULT 0,
  `errors` json DEFAULT NULL,
  `status` enum('processing','completed','failed') NOT NULL DEFAULT 'processing',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `import_batches_user_id_foreign` (`user_id`),
  CONSTRAINT `import_batches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 15. TABEL PERMISSIONS (LARAVEL SPATIE)
-- ============================================

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `model_id`, `model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  PRIMARY KEY (`permission_id`, `role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 16. TABEL CACHE & SESSIONS (LARAVEL)
-- ============================================

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 17. TABEL JOBS & FAILED JOBS (LARAVEL)
-- ============================================

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `available_at` int(11) NOT NULL,
  `reserved_at` int(11) DEFAULT NULL,
  `reserved_until` int(11) DEFAULT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` json DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL UNIQUE,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

-- ============================================
-- INSERT DATA DUMMY
-- ============================================

-- Insert Sifat Surat
INSERT INTO `sifat_surats` (`nama`, `keterangan`, `created_at`, `updated_at`) VALUES
('Biasa', 'Surat dengan sifat biasa', NOW(), NOW()),
('Penting', 'Surat dengan tingkat kepentingan tinggi', NOW(), NOW()),
('Rahasia', 'Surat dengan klasifikasi rahasia', NOW(), NOW());

-- Insert Units (Unit Kerja Kecamatan)
INSERT INTO `units` (`kode_unit`, `nama_unit`, `deskripsi`, `is_active`, `created_at`, `updated_at`) VALUES
('KEC-001', 'Kecamatan Sukamaju', 'Kantor Kecamatan Sukamaju', 1, NOW(), NOW()),
('SEK-001', 'Sekretariat Kecamatan', 'Unit sekretariat dan administrasi umum', 1, NOW(), NOW()),
('KEU-001', 'Sub Bagian Keuangan', 'Pengelolaan keuangan dan anggaran', 1, NOW(), NOW()),
('PEM-001', 'Seksi Pemerintahan', 'Bidang pemerintahan dan ketertiban', 1, NOW(), NOW()),
('EKI-001', 'Seksi Ekonomi & Pembangunan', 'Bidang ekonomi dan pembangunan', 1, NOW(), NOW()),
('KES-001', 'Seksi Kesejahteraan Rakyat', 'Bidang kesejahteraan sosial masyarakat', 1, NOW(), NOW()),
('YAN-001', 'Seksi Pelayanan Umum', 'Pelayanan administrasi kependudukan', 1, NOW(), NOW()),
('TRB-001', 'Kelurahan Terbang', 'Kelurahan wilayah utara', 1, NOW(), NOW()),
('TRI-001', 'Kelurahan Maju Jaya', 'Kelurahan wilayah tengah', 1, NOW(), NOW()),
('SUK-001', 'Kelurahan Sukaramai', 'Kelurahan wilayah selatan', 1, NOW(), NOW());

-- Insert Klasifikasi Arsip (Hierarki)
INSERT INTO `klasifikasi_arsip` (`parent_id`, `kode`, `nama`, `deskripsi`, `level`, `retensi_aktif`, `retensi_inaktif`, `is_active`, `created_at`, `updated_at`) VALUES
(NULL, '000', 'Umum', 'Klasifikasi umum', 1, 2, 5, 1, NOW(), NOW()),
(NULL, '100', 'Organisasi & Tata Laksana', 'Klasifikasi organisasi', 1, 5, 10, 1, NOW(), NOW()),
(NULL, '200', 'Kepegawaian', 'Klasifikasi kepegawaian', 1, 5, 10, 1, NOW(), NOW()),
(NULL, '300', 'Keuangan', 'Klasifikasi keuangan', 1, 5, 10, 1, NOW(), NOW()),
(NULL, '400', 'Pemerintahan', 'Klasifikasi pemerintahan', 1, 5, 10, 1, NOW(), NOW()),
(NULL, '500', 'Pembangunan', 'Klasifikasi pembangunan', 1, 5, 10, 1, NOW(), NOW()),
(NULL, '600', 'Kesejahteraan Rakyat', 'Klasifikasi kesra', 1, 5, 10, 1, NOW(), NOW()),
(1, '000.1', 'Surat Menyurat', 'Korespondensi umum', 2, 2, 5, 1, NOW(), NOW()),
(1, '000.2', 'Undangan', 'Undangan kegiatan', 2, 2, 5, 1, NOW(), NOW()),
(2, '100.1', 'Struktur Organisasi', 'Dokumen struktur organisasi', 2, 10, 20, 1, NOW(), NOW()),
(3, '200.1', 'Pendidikan & Pelatihan', 'Diklat pegawai', 2, 5, 10, 1, NOW(), NOW()),
(3, '200.2', 'Cuti & Izin', 'Administrasi cuti pegawai', 2, 5, 10, 1, NOW(), NOW()),
(4, '300.1', 'Anggaran', 'Dokumen anggaran APBD', 2, 5, 10, 1, NOW(), NOW()),
(4, '300.2', 'Pertanggungjawaban', 'Laporan pertanggungjawaban keuangan', 2, 5, 10, 1, NOW(), NOW()),
(5, '400.1', 'Kependudukan', 'Administrasi kependudukan', 2, 5, 10, 1, NOW(), NOW()),
(5, '400.2', 'Pertanahan', 'Administrasi pertanahan', 2, 5, 10, 1, NOW(), NOW()),
(6, '500.1', 'Infrastruktur', 'Pembangunan infrastruktur', 2, 5, 10, 1, NOW(), NOW()),
(6, '500.2', 'Ekonomi', 'Pengembangan ekonomi masyarakat', 2, 5, 10, 1, NOW(), NOW()),
(7, '600.1', 'Kesehatan', 'Program kesehatan masyarakat', 2, 5, 10, 1, NOW(), NOW()),
(7, '600.2', 'Pendidikan', 'Program pendidikan', 2, 5, 10, 1, NOW(), NOW());

-- Insert Users (Data Pegawai)
INSERT INTO `users` (`name`, `email`, `password`, `unit_id`, `nip`, `jabatan`, `telepon`, `is_active`, `created_at`, `updated_at`) VALUES
('Dr. Ahmad Fauzi, S.IP, M.Si', 'camat@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 1, '197501012000031001', 'Camat', '081234567890', 1, NOW(), NOW()),
('Siti Nurhaliza, S.AP', 'sekretaris@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 2, '198202152005012002', 'Sekretaris Kecamatan', '081234567891', 1, NOW(), NOW()),
('Budi Santoso, S.E', 'keuangan@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 3, '198505202010011003', 'Kepala Sub Bagian Keuangan', '081234567892', 1, NOW(), NOW()),
('Rina Wijaya, S.IP', 'pemerintahan@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 4, '198807122012012004', 'Kepala Seksi Pemerintahan', '081234567893', 1, NOW(), NOW()),
('Andi Pratama, S.T', 'ekobang@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 5, '199003082015011005', 'Kepala Seksi Ekonomi & Pembangunan', '081234567894', 1, NOW(), NOW()),
('Dewi Lestari, S.Sos', 'kesra@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 6, '198706252014012006', 'Kepala Seksi Kesejahteraan Rakyat', '081234567895', 1, NOW(), NOW()),
('Muhammad Rizki, S.Kom', 'pelayanan@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 7, '199201152018011007', 'Kepala Seksi Pelayanan Umum', '081234567896', 1, NOW(), NOW()),
('Eka Putri, A.Md', 'kelurahan.terbang@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 8, '199105202019012008', 'Lurah Terbang', '081234567897', 1, NOW(), NOW()),
('Joko Susilo, S.IP', 'kelurahan.majujaya@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 9, '198909102017011009', 'Lurah Maju Jaya', '081234567898', 1, NOW(), NOW()),
('Fitri Handayani, S.E', 'kelurahan.sukaramai@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 10, '199302282020012010', 'Lurah Sukaramai', '081234567899', 1, NOW(), NOW()),
('Admin Persuratan', 'admin.persuratan@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 2, '199506152021011011', 'Staf Administrasi Persuratan', '081234567800', 1, NOW(), NOW()),
('Staff Masuk', 'staff.masuk@sukamaju.go.id', '$2y$12$L0vJkTqRzN9M8hFpGxVwO.5KjYnH6QZ3XmWpRtYuIoPlKjHgFdScAe', 2, '199608202022011012', 'Staff Surat Masuk', '081234567801', 1, NOW(), NOW());

-- Insert Roles
INSERT INTO `roles` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('Super Admin', 'web', NOW(), NOW()),
('Camat', 'web', NOW(), NOW()),
('Sekretaris', 'web', NOW(), NOW()),
('Kasubag', 'web', NOW(), NOW()),
('Kasi', 'web', NOW(), NOW()),
('Lurah', 'web', NOW(), NOW()),
('Staff', 'web', NOW(), NOW());

-- Insert Permissions
INSERT INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`) VALUES
('surat_masuk_view', 'web', NOW(), NOW()),
('surat_masuk_create', 'web', NOW(), NOW()),
('surat_masuk_edit', 'web', NOW(), NOW()),
('surat_masuk_delete', 'web', NOW(), NOW()),
('surat_keluar_view', 'web', NOW(), NOW()),
('surat_keluar_create', 'web', NOW(), NOW()),
('surat_keluar_edit', 'web', NOW(), NOW()),
('surat_keluar_delete', 'web', NOW(), NOW()),
('disposisi_create', 'web', NOW(), NOW()),
('disposisi_view', 'web', NOW(), NOW()),
('arsip_view', 'web', NOW(), NOW()),
('laporan_view', 'web', NOW(), NOW()),
('user_manage', 'web', NOW(), NOW());

-- Assign Roles to Users
INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1), -- Camat as Super Admin
(2, 'App\\Models\\User', 1), -- Camat
(3, 'App\\Models\\User', 2), -- Sekretaris
(4, 'App\\Models\\User', 3), -- Kasubag Keuangan
(5, 'App\\Models\\User', 4), -- Kasi Pemerintahan
(5, 'App\\Models\\User', 5), -- Kasi Ekobang
(5, 'App\\Models\\User', 6), -- Kasi Kesra
(5, 'App\\Models\\User', 7), -- Kasi Pelayanan
(6, 'App\\Models\\User', 8), -- Lurah Terbang
(6, 'App\\Models\\User', 9), -- Lurah Maju Jaya
(6, 'App\\Models\\User', 10), -- Lurah Sukaramai
(7, 'App\\Models\\User', 11), -- Admin Persuratan
(7, 'App\\Models\\User', 12); -- Staff Masuk

-- Insert Agenda Counters
INSERT INTO `agenda_counters` (`unit_code`, `year`, `last_number`, `created_at`, `updated_at`) VALUES
('KEC-001', 2025, 150, NOW(), NOW()),
('SEK-001', 2025, 85, NOW(), NOW()),
('PEM-001', 2025, 45, NOW(), NOW()),
('EKI-001', 2025, 30, NOW(), NOW()),
('KES-001', 2025, 25, NOW(), NOW()),
('YAN-001', 2025, 120, NOW(), NOW());

-- Insert Surat Masuk (Dummy Data)
INSERT INTO `surat_masuk` (`nomor_agenda`, `nomor_surat`, `tanggal_surat`, `tanggal_terima`, `pengirim`, `jabatan_pengirim`, `perihal`, `klasifikasi_id`, `sifat_id`, `sifat`, `status`, `prioritas`, `isi_ringkas`, `unit_id`, `created_at`, `updated_at`) VALUES
('400/SM-001/2025', '005/DINKES/2025', '2025-01-15', '2025-01-16', 'Dinas Kesehatan Kabupaten', 'Kepala Dinas', 'Undangan Sosialisasi Program Kesehatan Masyarakat', 11, 1, 'biasa', 'didisposisi', 'Normal', 'Undangan sosialisasi program kesehatan untuk seluruh kecamatan', 6, NOW(), NOW()),
('400/SM-002/2025', '098/DIKBUD/2025', '2025-01-18', '2025-01-18', 'Dinas Pendidikan dan Kebudayaan', 'Kepala Dinas', 'Permohonan Data Peserta Didik', 12, 2, 'penting', 'selesai', 'Tinggi', 'Permohonan data siswa per kelurahan untuk tahun ajaran baru', 7, NOW(), NOW()),
('400/SM-003/2025', '120/PU/2025', '2025-01-20', '2025-01-21', 'Dinas Pekerjaan Umum', 'Sekretaris Dinas', 'Pemberitahuan Jadwal Perbaikan Jalan', 9, 1, 'biasa', 'dibaca', 'Normal', 'Informasi perbaikan jalan di wilayah kecamatan', 5, NOW(), NOW()),
('400/SM-004/2025', '050/BAPPEDA/2025', '2025-01-22', '2025-01-22', 'Bappeda Kabupaten', 'Kepala Bappeda', 'Undangan Musrenbang Kecamatan', 1, 2, 'penting', 'didisposisi', 'Tinggi', 'Undangan musyawarah perencanaan pembangunan tingkat kecamatan', 5, NOW(), NOW()),
('400/SM-005/2025', '003/KECAMATAN/2025', '2025-01-25', '2025-01-25', 'Kecamatan Sejahtera', 'Camat', 'Surat Edaran Protokol Kegiatan', 8, 1, 'biasa', 'selesai', 'Normal', 'Edaran protokol pelaksanaan kegiatan kecamatan', 1, NOW(), NOW()),
('400/SM-006/2025', '110/KAPOLSEK/2025', '2025-02-01', '2025-02-01', 'Kepolisian Sektor', 'Kapolsek', 'Imbauan Kamtibmas', 1, 2, 'penting', 'baru', 'Segera', 'Imbauan menjaga keamanan dan ketertiban masyarakat', 4, NOW(), NOW()),
('400/SM-007/2025', '025/DISDUKCAPIL/2025', '2025-02-05', '2025-02-05', 'Dinas Kependudukan dan Pencatatan Sipil', 'Kepala Dinas', 'Sosialisasi KTP Elektronik', 13, 1, 'biasa', 'baru', 'Normal', 'Sosialisasi pembuatan KTP-el bagi penduduk yang belum memiliki', 7, NOW(), NOW()),
('400/SM-008/2025', '078/SETDA/2025', '2025-02-10', '2025-02-10', 'Sekretariat Daerah Kabupaten', 'Sekda', 'Instruksi Bupati Tentang Pelayanan Publik', 10, 3, 'rahasia', 'baru', 'Segera', 'Instruksi peningkatan kualitas pelayanan publik', 2, NOW(), NOW());

-- Insert Surat Keluar (Dummy Data)
INSERT INTO `surat_keluar` (`nomor_surat`, `tanggal_surat`, `tujuan`, `jabatan_tujuan`, `perihal`, `klasifikasi_id`, `sifat_id`, `status`, `unit_id`, `unit_pembuat_id`, `created_by`, `isi_ringkas`, `created_at`, `updated_at`) VALUES
('400/SK-001/2025', '2025-01-17', 'Seluruh Lurah', 'Lurah', 'Undangan Rapat Koordinasi', 8, 1, 'tertandatangani', 2, 2, 2, 'Undangan rapat koordinasi bulanan dengan seluruh lurah', NOW(), NOW()),
('400/SK-002/2025', '2025-01-23', 'Dinas Kesehatan Kabupaten', 'Kepala Dinas', 'Konfirmasi Kehadiran', 11, 1, 'terkirim', 6, 6, 6, 'Konfirmasi kehadiran dalam sosialisasi program kesehatan', NOW(), NOW()),
('400/SK-003/2025', '2025-01-28', 'Bappeda Kabupaten', 'Kepala Bappeda', 'Daftar Usulan Program', 14, 2, 'tertandatangani', 5, 5, 5, 'Usulan program pembangunan untuk Musrenbang', NOW(), NOW()),
('400/SK-004/2025', '2025-02-02', 'Seluruh RT/RW', 'Ketua RT/RW', 'Edaran Kerja Bakti', 1, 1, 'siap_ttd', 4, 4, 4, 'Edaran kerja bakti membersihkan lingkungan', NOW(), NOW()),
('400/SK-005/2025', '2025-02-06', 'Dinas Pendidikan dan Kebudayaan', 'Kepala Dinas', 'Laporan Data Siswa', 12, 1, 'terkirim', 7, 7, 7, 'Laporan data peserta didik per kelurahan', NOW(), NOW()),
('400/SK-006/2025', '2025-02-11', 'Sekretariat Daerah Kabupaten', 'Sekda', 'Laporan Pelaksanaan Instruksi', 10, 2, 'review', 2, 2, 2, 'Laporan tindak lanjut instruksi bupati tentang pelayanan publik', NOW(), NOW());

-- Insert Disposisi (Dummy Data)
INSERT INTO `disposisi` (`surat_masuk_id`, `dari_user_id`, `ke_user_id`, `instruksi`, `batas_waktu`, `prioritas`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 'Harap dihadiri dan buat laporan hasil sosialisasi', '2025-01-20', 'Normal', 'Sudah Dibaca', NOW(), NOW()),
(2, 1, 7, 'Segera kumpulkan data sesuai permintaan', '2025-01-25', 'Tinggi', 'Selesai', NOW(), NOW()),
(4, 1, 5, 'Siapkan materi usulan untuk Musrenbang', '2025-01-30', 'Tinggi', 'Sedang Ditindaklanjuti', NOW(), NOW()),
(6, 1, 4, 'Koordinasikan dengan Polsek untuk tindakan lanjutan', '2025-02-05', 'Segera', 'Belum Dibaca', NOW(), NOW()),
(7, 1, 7, 'Laksanakan sosialisasi di setiap kelurahan', '2025-02-15', 'Normal', 'Belum Dibaca', NOW(), NOW()),
(8, 1, 2, 'Tindak lanjuti instruksi ini kepada semua unit', '2025-02-15', 'Segera', 'Belum Dibaca', NOW(), NOW());

-- Insert Template Disposisi
INSERT INTO `template_disposisi` (`user_id`, `nama`, `instruksi_default`, `tujuan_default`, `tembusan_default`, `created_at`, `updated_at`) VALUES
(1, 'Template Standar', 'Mohon diproses sesuai ketentuan', NULL, NULL, NOW(), NOW()),
(1, 'Template Urgent', 'Segera ditindaklanjuti dan laporkan hasilnya', NULL, NULL, NOW(), NOW()),
(2, 'Template Sekretaris', 'Koordinasikan dengan unit terkait', NULL, NULL, NOW(), NOW());

-- Insert Delegasi (Dummy Data)
INSERT INTO `delegasi` (`user_id`, `pengganti_user_id`, `tanggal_mulai`, `tanggal_selesai`, `is_active`, `keterangan`, `created_at`, `updated_at`) VALUES
(1, 2, '2025-03-01', '2025-03-07', 0, 'Camat mengikuti pelatihan kepemimpinan', NOW(), NOW());

-- Insert Lampiran (Dummy Data - Contoh)
INSERT INTO `lampiran` (`attachable_type`, `attachable_id`, `filename`, `original_name`, `mime_type`, `file_size`, `created_at`, `updated_at`) VALUES
('App\\Models\\SuratMasuk', 1, 'undangan_kesehatan.pdf', 'Undangan Sosialisasi Kesehatan.pdf', 'application/pdf', 245678, NOW(), NOW()),
('App\\Models\\SuratMasuk', 2, 'permohonan_data.xlsx', 'Format Data Siswa.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 89012, NOW(), NOW()),
('App\\Models\\SuratKeluar', 1, 'undangan_rakor.pdf', 'Undangan Rapat Koordinasi.pdf', 'application/pdf', 156789, NOW(), NOW());

-- Insert Audit Logs (Contoh Aktivitas)
INSERT INTO `audit_logs` (`user_id`, `action`, `entity`, `entity_id`, `old_values`, `new_values`, `ip_address`, `created_at`) VALUES
(11, 'created', 'App\\Models\\SuratMasuk', 1, NULL, '{"nomor_agenda": "400/SM-001/2025"}', '192.168.1.100', NOW()),
(11, 'created', 'App\\Models\\SuratMasuk', 2, NULL, '{"nomor_agenda": "400/SM-002/2025"}', '192.168.1.100', NOW()),
(1, 'updated', 'App\\Models\\SuratMasuk', 1, '{"status": "baru"}', '{"status": "didisposisi"}', '192.168.1.50', NOW()),
(2, 'created', 'App\\Models\\SuratKeluar', 1, NULL, '{"nomor_surat": "400/SK-001/2025"}', '192.168.1.51', NOW());

-- Insert Notification Preferences
INSERT INTO `notification_preferences` (`user_id`, `email_enabled`, `frequency`, `types`, `created_at`, `updated_at`) VALUES
(1, 1, 'immediate', '["disposisi", "surat_baru", "reminder"]', NOW(), NOW()),
(2, 1, 'daily_digest', '["disposisi", "surat_baru"]', NOW(), NOW());

COMMIT;
