# Audit Trail & Error Handling SIAP-SMK

## 📋 Ringkasan Modul

Modul ini menyediakan fitur lengkap untuk:
1. **Audit Trail** - Pelacakan semua perubahan data
2. **Error Handling** - Penanganan error user-friendly
3. **Import Data** - Import massal dengan preview dan validasi

---

## 🔍 1. Audit Trail Service

### Fitur
- ✅ Observer pattern pada Model: SuratMasuk, SuratKeluar, Disposisi, User, Lampiran, Delegasi
- ✅ Catat setiap CREATE, UPDATE, DELETE
- ✅ Field: timestamp, user_id, action, entity, entity_id, old_values (JSON), new_values (JSON), ip_address, user_agent
- ✅ Append-only: tidak bisa edit/hapus dari aplikasi
- ✅ Retention: 5 tahun (scheduler hapus otomatis)

### File yang Dibuat

#### Models
- `app/Models/AuditLog.php` - Model audit log dengan diff support
- `app/Models/ImportBatch.php` - Model tracking import batch

#### Observers
- `app/Observers/SuratMasukObserver.php`
- `app/Observers/SuratKeluarObserver.php`
- `app/Observers/DisposisiObserver.php`
- `app/Observers/UserObserver.php`
- `app/Observers/LampiranObserver.php`
- `app/Observers/DelegasiObserver.php`

#### Services
- `app/Services/ImportService.php` - Service untuk preview dan import data

#### Controllers
- `app/Http/Controllers/Admin/SystemController.php` - Controller admin untuk audit trail, error logs, import

#### Views
- `resources/views/admin/audit-trail/index.blade.php` - Halaman audit trail dengan filter dan diff view
- `resources/views/admin/error-logs/index.blade.php` - Halaman error logs
- `resources/views/admin/import/index.blade.php` - Halaman import dengan preview
- `resources/views/errors/custom.blade.php` - Error page custom (404, 403, 500, 503)

#### Exception Handler
- `app/Exceptions/Handler.php` - Custom exception handler

#### Migration
- `database/migrations/2026_06_21_060000_create_audit_and_import_tables.php`

---

## ⚙️ 2. Error Handling

### Custom Exception Handler
File: `app/Exceptions/Handler.php`

**Fitur:**
- User-friendly error page untuk 404, 403, 500, 503
- Pesan dalam Bahasa Indonesia
- Tombol kembali ke dashboard
- Technical log di `storage/logs/error.log`
- Level ERROR/CRITICAL dengan trace dan context

### Error Pages
File: `resources/views/errors/custom.blade.php`

**Tampilan:**
- Ikon sesuai kode error
- Pesan human-readable
- Timestamp error
- Responsive design

---

## 📥 3. Import Data Awal

### Fitur Import
- Template Excel/CSV untuk: surat masuk, surat keluar, klasifikasi, pengguna
- Upload → Preview (10 baris pertama) → Validasi → Commit/Rollback
- Error per baris ditampilkan
- Baris valid tetap diproses (partial import)
- Tracking batch import

### Alur Import
```
1. User pilih tipe data & upload file
2. Klik "Preview Data" → AJAX preview 10 baris
3. User verifikasi preview
4. Klik "Import Data" → Validasi & proses
5. Tampilkan hasil: sukses/gagal per baris
6. Simpan riwayat import
```

---

## 🛣️ Routes

```php
// Audit Trail (Admin TU only)
GET  /admin/audit-trail              - Halaman audit trail
GET  /admin/audit-trail/export       - Export Excel/CSV

// Error Logs (Admin only)
GET  /admin/error-logs               - Lihat error logs

// Import Data
GET  /admin/import                   - Halaman import
POST /admin/import/preview           - Preview file
POST /admin/import/process           - Proses import
GET  /admin/import/history/{id?}     - Riwayat import
```

---

## 📊 Cara Menggunakan

### 1. Jalankan Migration
```bash
php artisan migrate
```

### 2. Daftarkan Observers (di AppServiceProvider)
```php
// app/Providers/AppServiceProvider.php
public function boot(): void
{
    \App\Models\SuratMasuk::observe(\App\Observers\SuratMasukObserver::class);
    \App\Models\SuratKeluar::observe(\App\Observers\SuratKeluarObserver::class);
    \App\Models\Disposisi::observe(\App\Observers\DisposisiObserver::class);
    \App\Models\User::observe(\App\Observers\UserObserver::class);
    \App\Models\Lampiran::observe(\App\Observers\LampiranObserver::class);
    \App\Models\Delegasi::observe(\App\Observers\DelegasiObserver::class);
}
```

### 3. Setup Scheduler (untuk retention 5 tahun)
```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    $schedule->call(function () {
        \App\Models\AuditLog::pruneOldLogs();
    })->daily()->at('02:00');
}
```

### 4. Akses Halaman
- Audit Trail: `http://localhost/admin/audit-trail`
- Error Logs: `http://localhost/admin/error-logs`
- Import Data: `http://localhost/admin/import`

---

## 🔒 Security

- **Audit Trail**: Hanya Admin dan Staff TU yang bisa akses
- **Error Logs**: Hanya Admin yang bisa akses
- **Import**: Validasi file type, size max 10MB, validasi data per baris
- **Append-only**: Audit log tidak bisa diupdate/dihapus via aplikasi

---

## 📁 Struktur Database

### Tabel `audit_logs`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | User yang melakukan aksi |
| action | string | created/updated/deleted |
| entity | string | Class name model |
| entity_id | bigint | ID record |
| old_values | json | Data sebelum perubahan |
| new_values | json | Data setelah perubahan |
| ip_address | string | IP user |
| user_agent | text | Browser user |
| created_at | timestamp | Waktu aksi |

### Tabel `import_batches`
| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | User yang import |
| type | string | Tipe data |
| filename | string | Nama file |
| total_rows | integer | Total baris |
| success_count | integer | Baris sukses |
| failed_count | integer | Baris gagal |
| errors | json | Detail error per baris |
| status | enum | processing/completed/failed |

---

## 🎯 Contoh Penggunaan

### Log Manual dari Code
```php
use App\Models\AuditLog;

// Log custom action
AuditLog::log('approved', $surat, ['status' => 'pending'], ['status' => 'approved']);
```

### Query Audit Log
```php
// Filter by entity
$logs = AuditLog::forEntity(App\Models\SuratMasuk::class)
    ->betweenDates('2026-01-01', '2026-06-21')
    ->with('user')
    ->latest()
    ->get();
```

### Diff View di Blade
```blade
@foreach($log->diff as $field => $change)
    {{ $field }}: {{ $change['old'] }} → {{ $change['new'] }}
@endforeach
```

---

## 🧪 Testing

### Test Audit Trail
1. Login sebagai admin
2. Buat/edit/hapus surat
3. Buka `/admin/audit-trail`
4. Verifikasi log muncul dengan detail perubahan

### Test Error Handling
1. Akses URL yang tidak ada → 404 page
2. Akses tanpa izin → 403 page
3. Cek `storage/logs/error.log` untuk technical log

### Test Import
1. Siapkan file CSV/Excel dengan data valid dan invalid
2. Upload di `/admin/import`
3. Preview → verifikasi 10 baris pertama
4. Import → cek hasil sukses/gagal
5. Lihat riwayat di `/admin/import/history`

---

## 📝 Catatan

- Audit log disimpan selamanya (kecuali scheduler prune 5 tahun)
- Import partial: baris error tidak menggagalkan seluruh batch
- Error log di-rotate otomatis oleh Laravel
- Semua timestamp menggunakan timezone aplikasi

---

**Dibuat untuk SIAP-SMK v1.0**
