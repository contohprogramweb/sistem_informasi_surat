# Modul Arsip & Retensi - SIAP-SMK

## Overview
Modul Arsip & Retensi mengelola siklus hidup arsip surat masuk dan keluar, termasuk:
- Pengarsipan otomatis dengan perhitungan retensi
- Laporan jatuh tempo retensi
- Pemusnahan arsip dengan berita acara
- Soft delete dengan masa pemulihan 30 hari
- Scheduler untuk pemeliharaan otomatis

## Fitur Utama

### 1. Pengarsipan Surat
- **Surat Masuk**: Dapat diarsipkan jika semua disposisi selesai atau tidak perlu disposisi
- **Surat Keluar**: Dapat diarsipkan jika status "Terkirim"
- Sistem menghitung tanggal jatuh tempo aktif dan inaktif berdasarkan klasifikasi arsip
- Status berubah menjadi "Diarsipkan" dan surat menjadi read-only

### 2. Retensi Arsip
- **Retensi Aktif**: Masa arsip masih aktif digunakan (dalam tahun)
- **Retensi Inaktif**: Masa arsip disimpan sebelum dimusnahkan (dalam tahun)
- Perhitungan otomatis berdasarkan klasifikasi arsip

### 3. Laporan Jatuh Tempo
- Filter berdasarkan bulan ke depan (1-12 bulan)
- Filter berdasarkan tipe (aktif/inaktif/semua)
- Export CSV untuk laporan
- Menampilkan sisa bulan sebelum jatuh tempo

### 4. Pemusnahan Arsip
- Form berita acara pemusnahan
- Nomor berita acara otomatis (BA/YYYY/MM/NNNN)
- Daftar arsip yang dimusnahkan
- Status arsip → "Dimusnahkan"
- Lampiran fisik tetap ada untuk audit

### 5. Soft Delete
- Tombol "Hapus" pada surat aktif → status "Dihapus"
- Retention 30 hari sebelum hapus permanen
- Selama 30 hari, Admin TU bisa "Pulihkan"
- Setelah 30 hari, hapus permanen termasuk lampiran fisik
- Audit log untuk setiap penghapusan

### 6. Scheduler (Laravel Command)
**Command**: `php artisan arsip:retention-scheduler`

**Opsi**:
- `--permanent-delete`: Hapus permanen soft delete > 30 hari
- `--send-notifications`: Kirim notifikasi retensi
- `--months-ahead=N`: Jumlah bulan ahead untuk notifikasi (default: 3)

**Jadwal Otomatis** (di `routes/console.php`):
```php
// Daily: Hapus permanen + notifikasi
Schedule::command('arsip:retention-scheduler')->dailyAt('01:00');

// Daily: Notifikasi retensi (3 bulan sebelum)
Schedule::command('arsip:retention-scheduler --send-notifications --months-ahead=3')
    ->dailyAt('08:00');

// Weekly: Hapus permanen saja
Schedule::command('arsip:retention-scheduler --permanent-delete')
    ->weeklyOn(1, '02:00');
```

## Struktur Database

### Tabel yang Ditambahkan

#### 1. Kolom baru di `surat_masuk` dan `surat_keluar`:
- `tanggal_arsip` (date): Tanggal pengarsipan
- `tanggal_jatuh_aktif` (date): Tanggal jatuh tempo aktif
- `tanggal_jatuh_inaktif` (date): Tanggal jatuh tempo inaktif
- `status_arsip` (enum): 'aktif', 'inaktif', 'dimusnahkan'
- `alasan_hapus` (text): Alasan penghapusan
- `dimusnahkan_at` (timestamp): Tanggal pemusnahan
- `dimusnahkan_by` (foreign key): User yang memusnahkan

#### 2. `berita_acara_pemusnahan`:
- `id`
- `nomor_berita_acara` (unique)
- `tanggal_berita_acara`
- `keterangan`
- `created_by`
- `approved_at`
- `approved_by`
- `timestamps`, `soft_deletes`

#### 3. `berita_acara_detail`:
- `id`
- `berita_acara_id` (FK)
- `arsip_id`, `arsip_type` (polymorphic)
- `nomor_surat`, `tanggal_surat`, `perihal`
- `retensi_aktif_tahun`, `retensi_inaktif_tahun`
- `tanggal_jatuh_tempo`
- `timestamps`

#### 4. `arsip_notifications`:
- `id`
- `arsip_id`, `arsip_type` (polymorphic)
- `type`: 'jatuh_tempo_aktif', 'jatuh_tempo_inaktif', 'reminder_pemusnahan'
- `bulan_sebelumnya`
- `is_read`
- `sent_at`
- `timestamps`

## Model Baru

### BeritaAcaraPemusnahan
- Generate nomor berita acara otomatis
- Relationship ke details
- Scope approved

### BeritaAcaraDetail
- Polymorphic relationship ke arsip
- Snapshot data arsip saat pemusnahan

### ArsipNotification
- Notifikasi jatuh tempo
- Mark as read

## Service Class: ArsipRetensiService

### Methods:
- `archiveSuratMasuk(SuratMasuk $surat)`: Arsipkan surat masuk
- `archiveSuratKeluar(SuratKeluar $surat)`: Arsipkan surat keluar
- `getJatuhTempoArsip(int $monthsAhead, string $type)`: Get arsip jatuh tempo
- `createBeritaAcara(array $data, array $arsipList)`: Buat berita acara
- `softDeleteSurat($surat, string $reason)`: Soft delete dengan alasan
- `restoreSurat($surat)`: Pulihkan dari soft delete
- `permanentDeleteExpiredSurat()`: Hapus permanen expired
- `sendRetentionNotifications(int $monthsAhead)`: Kirim notifikasi

## Controller: ArsipRetensiController

### Routes:
```
GET  /arsip                      -> index (dashboard)
POST /arsip/surat-masuk/{id}/archive  -> archiveSuratMasuk
POST /arsip/surat-keluar/{id}/archive -> archiveSuratKeluar
GET  /arsip/jatuh-tempo          -> jatuhTempo report
GET  /arsip/jatuh-tempo/export   -> exportJatuhTempo (CSV)
GET  /arsip/berita-acara         -> listBeritaAcara
GET  /arsip/berita-acara/create  -> createBeritaAcara form
POST /arsip/berita-acara         -> storeBeritaAcara
GET  /arsip/berita-acara/{id}    -> showBeritaAcara
GET  /arsip/trash                -> trash (soft deleted items)
PATCH /arsip/restore/{type}/{id} -> restore
DELETE /arsip/{type}/{id}        -> destroy (soft delete)
GET  /arsip/notifications        -> notifications
PATCH /arsip/notifications/{id}/read -> markNotificationAsRead
```

## Views

### Blade Templates:
- `resources/views/arsip/index.blade.php`: Dashboard arsip
- `resources/views/arsip/jatuh-tempo.blade.php`: Laporan jatuh tempo
- `resources/views/arsip/trash.blade.php`: Trash (soft deleted)
- `resources/views/arsip/notifications.blade.php`: Notifikasi
- `resources/views/arsip/pemusnahan/index.blade.php`: Daftar arsip siap dimusnahkan

## Permissions Required

- `arsip.manage`: Akses menu arsip
- `arsip.archive`: Melakukan pengarsipan
- `arsip.destroy`: Menghapus arsip
- `arsip.restore`: Memulihkan arsip
- `arsip.pemusnahan`: Membuat berita acara pemusnahan

## Cara Penggunaan

### 1. Instalasi
```bash
# Jalankan migration
php artisan migrate

# Setup scheduler di crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Mengarsipkan Surat
```php
// Di controller atau service
use App\Services\ArsipRetensiService;

$service = app(ArsipRetensiService::class);
$result = $service->archiveSuratMasuk($suratMasuk);

if ($result['success']) {
    // Surat berhasil diarsipkan
}
```

### 3. Manual Run Scheduler
```bash
# Run semua task
php artisan arsip:retention-scheduler

# Hanya hapus permanen
php artisan arsip:retention-scheduler --permanent-delete

# Hanya kirim notifikasi
php artisan arsip:retention-scheduler --send-notifications --months-ahead=6
```

### 4. Export Laporan Jatuh Tempo
```
GET /arsip/jatuh-tempo/export?months_ahead=3&type=all
```

## Error Handling

- Validasi sebelum pengarsipan (disposisi selesai, status terkirin)
- Rollback transaksi jika gagal
- Log audit untuk setiap operasi penting
- Notifikasi error ke user

## Audit Logging

Semua operasi dicatat di channel 'audit':
- Pengarsipan surat
- Pemusnahan arsip
- Soft delete
- Restore
- Permanent delete

## Catatan Penting

1. **File Fisik**: Saat permanent delete, file lampiran juga dihapus dari storage
2. **Berita Acara**: Data arsip di-snapshot saat pemusnahan untuk audit trail
3. **Retention Period**: 30 hari untuk soft delete dapat dikonfigurasi di model
4. **Scheduler**: Pastikan cron job berjalan untuk automation
