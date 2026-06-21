SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS notification_preferences;
DROP TABLE IF EXISTS arsip_notifications;
DROP TABLE IF EXISTS tte_logs;
DROP TABLE IF EXISTS tte_signatures;
DROP TABLE IF EXISTS berita_acara_detail;
DROP TABLE IF EXISTS berita_acara_pemusnahan;
DROP TABLE IF EXISTS import_batches;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS template_disposisi;
DROP TABLE IF EXISTS catatan_pribadi;
DROP TABLE IF EXISTS delegasi;
DROP TABLE IF EXISTS lampiran;
DROP TABLE IF EXISTS disposisi;
DROP TABLE IF EXISTS surat_keluar_histories;
DROP TABLE IF EXISTS surat_keluar;
DROP TABLE IF EXISTS surat_masuk_unit_tujuan;
DROP TABLE IF EXISTS surat_masuk;
DROP TABLE IF EXISTS agenda_counters;
DROP TABLE IF EXISTS role_has_permissions;
DROP TABLE IF EXISTS model_has_roles;
DROP TABLE IF EXISTS model_has_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS roles;
DROP TABLE IF EXISTS sifat_surats;
DROP TABLE IF EXISTS klasifikasi_arsip;
DROP TABLE IF EXISTS units;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS sessions;
DROP TABLE IF EXISTS password_reset_tokens;
DROP TABLE IF EXISTS cache;
DROP TABLE IF EXISTS cache_locks;
DROP TABLE IF EXISTS jobs;
DROP TABLE IF EXISTS job_batches;
DROP TABLE IF EXISTS failed_jobs;

SET FOREIGN_KEY_CHECKS = 1;

-- Create Master Data Tables
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE units (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_unit VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE klasifikasi_arsip (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(255) NOT NULL,
    parent_id INT UNSIGNED NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (parent_id) REFERENCES klasifikasi_arsip(id) ON DELETE CASCADE
);

CREATE TABLE sifat_surats (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(50) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE agenda_counters (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tahun YEAR NOT NULL,
    counter INT UNSIGNED NOT NULL DEFAULT 0,
    jenis ENUM('masuk', 'keluar') NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

-- Create Transaction Tables
CREATE TABLE surat_masuk (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    no_agenda INT UNSIGNED NOT NULL,
    no_surat VARCHAR(255) NOT NULL,
    tanggal_surat DATE NOT NULL,
    perihal TEXT NOT NULL,
    pengirim VARCHAR(255) NOT NULL,
    sifat_surat_id INT UNSIGNED NOT NULL,
    klasifikasi_arsip_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (sifat_surat_id) REFERENCES sifat_surats(id),
    FOREIGN KEY (klasifikasi_arsip_id) REFERENCES klasifikasi_arsip(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE surat_masuk_unit_tujuan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    surat_masuk_id INT UNSIGNED NOT NULL,
    unit_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (surat_masuk_id) REFERENCES surat_masuk(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id)
);

CREATE TABLE surat_keluar (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    no_agenda INT UNSIGNED NOT NULL,
    no_surat VARCHAR(255) NOT NULL,
    tanggal_surat DATE NOT NULL,
    perihal TEXT NOT NULL,
    tujuan VARCHAR(255) NOT NULL,
    sifat_surat_id INT UNSIGNED NOT NULL,
    klasifikasi_arsip_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (sifat_surat_id) REFERENCES sifat_surats(id),
    FOREIGN KEY (klasifikasi_arsip_id) REFERENCES klasifikasi_arsip(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE surat_keluar_histories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    surat_keluar_id INT UNSIGNED NOT NULL,
    status ENUM('dibuat', 'disetujui', 'dikirim', 'selesai') NOT NULL,
    catatan TEXT NULL,
    updated_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (surat_keluar_id) REFERENCES surat_keluar(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id)
);

CREATE TABLE disposisi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    surat_masuk_id INT UNSIGNED NOT NULL,
    dari_unit_id INT UNSIGNED NOT NULL,
    ke_unit_id INT UNSIGNED NOT NULL,
    isi_disposisi TEXT NOT NULL,
    catatan TEXT NULL,
    dibaca BOOLEAN DEFAULT FALSE,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (surat_masuk_id) REFERENCES surat_masuk(id) ON DELETE CASCADE,
    FOREIGN KEY (dari_unit_id) REFERENCES units(id),
    FOREIGN KEY (ke_unit_id) REFERENCES units(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE lampiran (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(255) NOT NULL,
    record_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE delegasi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    dari_user_id BIGINT UNSIGNED NOT NULL,
    ke_user_id BIGINT UNSIGNED NOT NULL,
    tanggal_mulai DATE NOT NULL,
    tanggal_selesai DATE NOT NULL,
    aktif BOOLEAN DEFAULT TRUE,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (dari_user_id) REFERENCES users(id),
    FOREIGN KEY (ke_user_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE catatan_pribadi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    judul VARCHAR(255) NOT NULL,
    isi TEXT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE template_disposisi (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_template VARCHAR(255) NOT NULL,
    isi_template TEXT NOT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE audit_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    event VARCHAR(255) NOT NULL,
    subject_type VARCHAR(255) NULL,
    subject_id INT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    url TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE import_batches (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_name VARCHAR(255) NOT NULL,
    total_records INT UNSIGNED NOT NULL,
    processed_records INT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('pending', 'processing', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL
);

CREATE TABLE berita_acara_pemusnahan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nomor_berita_acara VARCHAR(255) NOT NULL,
    tanggal_pelaksanaan DATE NOT NULL,
    tempat_pelaksanaan VARCHAR(255) NOT NULL,
    keterangan TEXT NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE berita_acara_detail (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    berita_acara_id INT UNSIGNED NOT NULL,
    klasifikasi_arsip_id INT UNSIGNED NOT NULL,
    jumlah_berkas INT UNSIGNED NOT NULL,
    keterangan TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (berita_acara_id) REFERENCES berita_acara_pemusnahan(id) ON DELETE CASCADE,
    FOREIGN KEY (klasifikasi_arsip_id) REFERENCES klasifikasi_arsip(id)
);

CREATE TABLE arsip_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'error') NOT NULL DEFAULT 'info',
    read_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE tte_signatures (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    table_name VARCHAR(255) NOT NULL,
    record_id INT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    signature_data TEXT NOT NULL,
    signed_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE tte_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tte_signature_id INT UNSIGNED NOT NULL,
    action VARCHAR(255) NOT NULL,
    details JSON NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (tte_signature_id) REFERENCES tte_signatures(id) ON DELETE CASCADE
);

CREATE TABLE notification_preferences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    notifikasi_surat_baru BOOLEAN DEFAULT TRUE,
    notifikasi_disposisi_baru BOOLEAN DEFAULT TRUE,
    notifikasi_surat_keluar_baru BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create Laravel System Tables
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (email)
);

CREATE TABLE sessions (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL
);

CREATE TABLE cache (
    key VARCHAR(255) NOT NULL PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
);

CREATE TABLE cache_locks (
    key VARCHAR(255) NOT NULL PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
);

CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts TINYINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL
);

CREATE TABLE job_batches (
    id VARCHAR(255) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids JSON NOT NULL,
    options JSON NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
);

CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Insert Dummy Data for Master Tables
INSERT INTO units (nama_unit, created_at, updated_at) VALUES
('Tata Usaha', NOW(), NOW()),
('Kurikulum', NOW(), NOW()),
('Kesiswaan', NOW(), NOW()),
('Humas & Alumni', NOW(), NOW()),
('Sarpras', NOW(), NOW()),
('Pengembangan Karakter', NOW(), NOW()),
('Pusat Karir', NOW(), NOW()),
('Perpustakaan', NOW(), NOW());

INSERT INTO klasifikasi_arsip (kode, nama, parent_id, created_at, updated_at) VALUES
('A', 'Kepegawaian', NULL, NOW(), NOW()),
('A.1', 'Penerimaan Pegawai', 1, NOW(), NOW()),
('A.2', 'Mutasi Pegawai', 1, NOW(), NOW()),
('B', 'Keuangan', NULL, NOW(), NOW()),
('B.1', 'Anggaran', 4, NOW(), NOW()),
('B.2', 'Realisasi', 4, NOW(), NOW()),
('C', 'Akademik', NULL, NOW(), NOW()),
('C.1', 'Rencana Pembelajaran', 7, NOW(), NOW()),
('C.2', 'Nilai Siswa', 7, NOW(), NOW()),
('D', 'Kesiswaan', NULL, NOW(), NOW()),
('D.1', 'Data Siswa', 10, NOW(), NOW()),
('D.2', 'Prestasi Siswa', 10, NOW(), NOW()),
('E', 'Administrasi Umum', NULL, NOW(), NOW()),
('E.1', 'Surat Masuk', 13, NOW(), NOW()),
('E.2', 'Surat Keluar', 13, NOW(), NOW()),
('F', 'Hukum & Perundangan', NULL, NOW(), NOW());

INSERT INTO sifat_surats (nama, created_at, updated_at) VALUES
('Biasa', NOW(), NOW()),
('Penting', NOW(), NOW()),
('Rahasia', NOW(), NOW()),
('Segera', NOW(), NOW());

INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
('view_users', 'web', NOW(), NOW()),
('create_users', 'web', NOW(), NOW()),
('edit_users', 'web', NOW(), NOW()),
('delete_users', 'web', NOW(), NOW()),
('view_surat_masuk', 'web', NOW(), NOW()),
('create_surat_masuk', 'web', NOW(), NOW()),
('edit_surat_masuk', 'web', NOW(), NOW()),
('delete_surat_masuk', 'web', NOW(), NOW()),
('view_surat_keluar', 'web', NOW(), NOW()),
('create_surat_keluar', 'web', NOW(), NOW()),
('edit_surat_keluar', 'web', NOW(), NOW()),
('delete_surat_keluar', 'web', NOW(), NOW()),
('view_disposisi', 'web', NOW(), NOW()),
('create_disposisi', 'web', NOW(), NOW()),
('edit_disposisi', 'web', NOW(), NOW()),
('delete_disposisi', 'web', NOW(), NOW()),
('view_laporan', 'web', NOW(), NOW()),
('view_admin', 'web', NOW(), NOW());

INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
('Super Admin', 'web', NOW(), NOW()),
('Kepala Sekolah', 'web', NOW(), NOW()),
('Wakasek', 'web', NOW(), NOW()),
('Kepala TU', 'web', NOW(), NOW()),
('Staf TU', 'web', NOW(), NOW()),
('Guru', 'web', NOW(), NOW());

INSERT INTO users (name, email, password, created_at, updated_at) VALUES
('Admin Sistem', 'admin@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()), -- password: password123
('Kepala Sekolah', 'kepala.sekolah@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Wakasek Kurikulum', 'wakasek.kuri@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Wakasek Kesiswaan', 'wakasek.kesiswaan@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Kepala Tata Usaha', 'katu@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Staf TU 1', 'staf1@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Staf TU 2', 'staf2@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Guru 1', 'guru1@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Guru 2', 'guru2@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW()),
('Guru 3', 'guru3@smk.sch.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NOW(), NOW());

INSERT INTO model_has_roles (role_id, model_type, model_id) VALUES
(1, 'App\\Models\\User', 1), -- Admin -> Super Admin
(2, 'App\\Models\\User', 2), -- Kepala Sekolah
(3, 'App\\Models\\User', 3), -- Wakasek Kurikulum
(3, 'App\\Models\\User', 4), -- Wakasek Kesiswaan
(4, 'App\\Models\\User', 5), -- Kepala TU
(5, 'App\\Models\\User', 6), -- Staf TU 1
(5, 'App\\Models\\User', 7), -- Staf TU 2
(6, 'App\\Models\\User', 8), -- Guru 1
(6, 'App\\Models\\User', 9), -- Guru 2
(6, 'App\\Models\\User', 10); -- Guru 3

INSERT INTO role_has_permissions (role_id, permission_id) VALUES
-- Super Admin gets all permissions
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8), (1, 9), (1, 10), (1, 11), (1, 12), (1, 13), (1, 14), (1, 15), (1, 16), (1, 17), (1, 18),
-- Kepala Sekolah
(2, 5), (2, 6), (2, 7), (2, 8), (2, 9), (2, 10), (2, 11), (2, 12), (2, 13), (2, 14), (2, 15), (2, 16), (2, 17),
-- Wakasek
(3, 5), (3, 6), (3, 7), (3, 8), (3, 9), (3, 10), (3, 11), (3, 12), (3, 13), (3, 14), (3, 15), (3, 16),
-- Kepala TU
(4, 5), (4, 6), (4, 7), (4, 8), (4, 9), (4, 10), (4, 11), (4, 12), (4, 13), (4, 14), (4, 15), (4, 16),
-- Staf TU
(5, 5), (5, 6), (5, 7), (5, 8), (5, 9), (5, 10), (5, 11), (5, 12), (5, 13), (5, 14), (5, 15),
-- Guru
(6, 5), (6, 9), (6, 17);

INSERT INTO agenda_counters (tahun, counter, jenis, created_at, updated_at) VALUES
(2026, 0, 'masuk', NOW(), NOW()),
(2026, 0, 'keluar', NOW(), NOW()),
(2025, 0, 'masuk', NOW(), NOW()),
(2025, 0, 'keluar', NOW(), NOW()),
(2024, 0, 'masuk', NOW(), NOW());

-- Insert Dummy Data for Transaction Tables
INSERT INTO surat_masuk (no_agenda, no_surat, tanggal_surat, perihal, pengirim, sifat_surat_id, klasifikasi_arsip_id, created_by, created_at, updated_at) VALUES
(1, 'SMK/001/I/2026', '2026-01-15', 'Undangan Pelatihan Guru', 'Dinas Pendidikan Provinsi', 1, 7, 1, NOW(), NOW()),
(2, 'SMK/002/I/2026', '2026-01-20', 'Pemberitahuan Jadwal Ujian Nasional', 'BSNP', 2, 7, 1, NOW(), NOW()),
(3, 'SMK/003/I/2026', '2026-01-22', 'Permohonan Data Siswa untuk Beasiswa', 'Lembaga Swadaya Masyarakat X', 1, 10, 1, NOW(), NOW()),
(4, 'SMK/004/I/2026', '2026-01-25', 'Hasil Evaluasi Program Keahlian', 'Lembaga Penilaian Mandiri', 2, 7, 1, NOW(), NOW()),
(5, 'SMK/005/II/2026', '2026-02-01', 'Pemberitahuan Revisi Anggaran', 'Dinas Keuangan Daerah', 2, 4, 1, NOW(), NOW()),
(6, 'SMK/006/II/2026', '2026-02-05', 'Surat Perintah Tugas', 'Kepala Sekolah', 1, 1, 2, NOW(), NOW()),
(7, 'SMK/007/II/2026', '2026-02-08', 'Laporan Kegiatan Ekstrakurikuler', 'Pembina OSIS', 1, 10, 1, NOW(), NOW()),
(8, 'SMK/008/II/2026', '2026-02-10', 'Permohonan Ijin Penggunaan Fasilitas', 'Komunitas Belajar', 1, 13, 1, NOW(), NOW());

INSERT INTO surat_masuk_unit_tujuan (surat_masuk_id, unit_id, created_at, updated_at) VALUES
(1, 2, NOW(), NOW()), -- Undangan pelatihan -> Kurikulum
(2, 2, NOW(), NOW()), -- Jadwal UN -> Kurikulum
(3, 3, NOW(), NOW()), -- Beasiswa -> Kesiswaan
(4, 2, NOW(), NOW()), -- Evaluasi -> Kurikulum
(5, 1, NOW(), NOW()), -- Anggaran -> TU
(6, 1, NOW(), NOW()), -- Tugas -> TU
(7, 3, NOW(), NOW()), -- Ekstra -> Kesiswaan
(8, 1, NOW(), NOW()); -- Fasilitas -> TU

INSERT INTO disposisi (surat_masuk_id, dari_unit_id, ke_unit_id, isi_disposisi, catatan, created_by, created_at, updated_at) VALUES
(1, 1, 2, 'Mohon dipersiapkan daftar guru yang akan ikut serta', 'Prioritaskan guru muda', 5, NOW(), NOW()),
(2, 1, 2, 'Harap segera didistribusikan ke wali kelas', NULL, 5, NOW(), NOW()),
(3, 1, 3, 'Silakan disiapkan data siswa yang memenuhi syarat', 'Sebelum 1 Maret', 5, NOW(), NOW()),
(4, 2, 1, 'Simpan sebagai arsip penting', 'Perlu ditindaklanjuti', 3, NOW(), NOW()),
(5, 1, 1, 'Arsipkan', NULL, 5, NOW(), NOW()),
(6, 1, 1, 'Arsipkan', NULL, 5, NOW(), NOW());

INSERT INTO surat_keluar (no_agenda, no_surat, tanggal_surat, perihal, tujuan, sifat_surat_id, klasifikasi_arsip_id, created_by, created_at, updated_at) VALUES
(1, 'SK/001/I/2026', '2026-01-18', 'Balasan Undangan Pelatihan', 'Dinas Pendidikan Provinsi', 1, 7, 1, NOW(), NOW()),
(2, 'SK/002/I/2026', '2026-01-22', 'Konfirmasi Jadwal Ujian', 'BSNP', 1, 7, 1, NOW(), NOW()),
(3, 'SK/003/I/2026', '2026-01-25', 'Persetujuan Beasiswa Siswa', 'Lembaga Swadaya Masyarakat X', 1, 10, 1, NOW(), NOW()),
(4, 'SK/004/II/2026', '2026-02-02', 'Surat Tugas Guru', 'PT ABC Training', 1, 1, 1, NOW(), NOW()),
(5, 'SK/005/II/2026', '2026-02-06', 'Laporan Realisasi Anggaran Semester Ganjil', 'Dinas Keuangan Daerah', 2, 4, 1, NOW(), NOW()),
(6, 'SK/006/II/2026', '2026-02-11', 'Ijin Penggunaan Fasilitas', 'Komunitas Belajar', 1, 13, 1, NOW(), NOW());

INSERT INTO surat_keluar_histories (surat_keluar_id, status, catatan, updated_by, created_at, updated_at) VALUES
(1, 'dibuat', 'Draft awal', 1, NOW(), NOW()),
(1, 'disetujui', 'Disetujui oleh Kepala Sekolah', 2, NOW(), NOW()),
(1, 'dikirim', 'Telah dikirim via email', 1, NOW(), NOW()),
(2, 'dibuat', 'Draft awal', 1, NOW(), NOW()),
(2, 'disetujui', 'Disetujui oleh Wakasek Kurikulum', 3, NOW(), NOW()),
(2, 'dikirim', 'Telah dikirim via pos', 1, NOW(), NOW()),
(3, 'dibuat', 'Draft awal', 1, NOW(), NOW()),
(3, 'disetujui', 'Disetujui oleh Kepala Sekolah', 2, NOW(), NOW()),
(4, 'dibuat', 'Draft awal', 1, NOW(), NOW()),
(5, 'dibuat', 'Draft awal', 1, NOW(), NOW()),
(6, 'dibuat', 'Draft awal', 1, NOW(), NOW());

INSERT INTO template_disposisi (nama_template, isi_template, created_by, created_after) VALUES
('Setuju & Arsipkan', 'Setuju, harap diarsipkan.', 1, NOW(), NOW()),
('Periksa Lebih Lanjut', 'Mohon dicek dan diproses lebih lanjut.', 1, NOW(), NOW()),
('Tindak Lanjuti', 'Agar ditindaklanjuti sebagaimana mestinya.', 2, NOW(), NOW());

INSERT INTO delegasi (dari_user_id, ke_user_id, tanggal_mulai, tanggal_selesai, created_by, created_at, updated_at) VALUES
(2, 5, '2026-02-01', '2026-02-28', 2, NOW(), NOW()); -- Kepsek delegasi ke Kepala TU

INSERT INTO catatan_pribadi (user_id, judul, isi, created_at, updated_at) VALUES
(1, 'Catatan Instalasi', 'Pastikan semua dependensi terinstall sebelum deploy', NOW(), NOW()),
(5, 'Jadwal Rapat Mingguan', 'Rapat mingguan hari Jumat jam 09.00 WIB', NOW(), NOW()),
(8, 'Materi Presentasi', 'Slide presentasi untuk pertemuan orang tua siswa', NOW(), NOW());

INSERT INTO notification_preferences (user_id, created_at, updated_at) VALUES
(1, NOW(), NOW()),
(2, NOW(), NOW()),
(5, NOW(), NOW()),
(6, NOW(), NOW()),
(8, NOW(), NOW());
