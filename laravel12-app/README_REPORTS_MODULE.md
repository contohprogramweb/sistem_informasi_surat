# Modul Laporan & Statistik SIAP-SMK

## Ringkasan Implementasi

Modul ini menyediakan fitur pelaporan dan analisis data untuk sistem SIAP-SMK dengan kemampuan export ke berbagai format.

## File yang Dibuat

### Controllers
- `app/Http/Controllers/Reports/ReportController.php` - Controller utama untuk semua laporan

### Models
- `app/Models/AuditLog.php` - Model untuk audit trail log

### Export Classes
- `app/Exports/RekapDisposisiExport.php` - Export Excel untuk rekap disposisi
- `app/Exports/ArsipJatuhTempoExport.php` - Export Excel untuk arsip jatuh tempo
- `app/Exports/AuditTrailExport.php` - Export Excel/CSV untuk audit trail

### Views
- `resources/views/reports/index.blade.php` - Menu utama laporan
- `resources/views/reports/buku-agenda/masuk.blade.php` - Buku agenda surat masuk
- `resources/views/reports/buku-agenda/keluar.blade.php` - Buku agenda surat keluar
- `resources/views/reports/rekap-disposisi/index.blade.php` - Rekap disposisi
- `resources/views/reports/arsip-jatuh-tempo/index.blade.php` - Arsip jatuh tempo
- `resources/views/reports/audit-trail/index.blade.php` - Audit trail log
- `resources/views/reports/statistik/dashboard.blade.php` - Dashboard statistik dengan grafik
- `resources/views/reports/pdf/buku-agenda.blade.php` - Template PDF buku agenda

### Routes
Ditambahkan di `routes/web.php`:
```php
Route::middleware(['auth', 'verified'])->prefix('reports')->name('reports.')->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/buku-agenda/masuk', ...)->name('buku-agenda.masuk');
    Route::get('/buku-agenda/keluar', ...)->name('buku-agenda.keluar');
    Route::get('/export/buku-agenda', ...)->name('export.buku-agenda');
    Route::get('/rekap-disposisi', ...)->name('rekap-disposisi');
    Route::get('/export/rekap-disposisi', ...)->name('export.rekap-disposisi');
    Route::get('/arsip-jatuh-tempo', ...)->name('arsip-jatuh-tempo');
    Route::get('/export/arsip-jatuh-tempo', ...)->name('export.arsip-jatuh-tempo');
    Route::get('/audit-trail', ...)->name('audit-trail');
    Route::get('/export/audit-trail', ...)->name('export.audit-trail');
    Route::get('/statistik', ...)->name('statistik');
});
```

## Fitur yang Diimplementasikan

### 1. Buku Agenda
**Surat Masuk:**
- Filter berdasarkan periode (bulan/tahun)
- Grouping by: Bulan atau Klasifikasi
- Tabel data lengkap dengan pagination
- Export PDF dengan kop surat instansi

**Surat Keluar:**
- Filter berdasarkan periode
- Grouping by: Status atau Unit Pembuat
- Export PDF

### 2. Rekap Disposisi
- Filter per unit dan rentang tanggal
- Ringkasan per unit dengan kolom:
  - Total disposisi
  - Selesai
  - Belum selesai
  - Overdue (melewati batas waktu)
- Detail disposisi dengan highlight overdue
- Export Excel (.xlsx)

### 3. Arsip Jatuh Tempo
- Filter status retensi (Aktif/Inaktif)
- Filter X bulan ke depan (default: 3 bulan)
- Stats cards menampilkan jumlah arsip jatuh tempo
- Highlight arsip yang urgent (< 30 hari)
- Kolom "Sisa Hari" dengan indikator warna
- Export Excel

### 4. Audit Trail Log
- **Akses terbatas**: Hanya Admin TU dan Admin
- Filter by:
  - User
  - Entity (contoh: SuratMasuk)
  - Aksi (create, update, delete, restore)
  - Rentang tanggal
- Tampilan diff view untuk perubahan data
- Export Excel dan CSV

### 5. Dashboard Statistik (Chart.js)
**Summary Cards:**
- Surat Masuk (total tahun berjalan)
- Surat Keluar
- Total Disposisi
- Disposisi Selesai
- Arsip Aktif
- Arsip Jatuh Tempo

**Grafik:**
1. **Bar Chart**: Surat Masuk & Keluar per Bulan
2. **Pie Chart**: Disposisi per Status
3. **Horizontal Bar Chart**: Arsip per Klasifikasi (Top 10)

- Dropdown pilih tahun
- Responsive design

## Dependencies

Pastikan package berikut terinstall:

```bash
# PDF Export
composer require barryvdh/laravel-dompdf

# Excel Export
composer require maatwebsite/excel
```

## Cara Penggunaan

1. **Akses Menu Laporan**
   ```
   http://localhost/reports
   ```

2. **Buku Agenda**
   ```
   http://localhost/reports/buku-agenda/masuk?periode=2026-06
   http://localhost/reports/buku-agenda/keluar?periode=2026-06
   ```

3. **Rekap Disposisi**
   ```
   http://localhost/reports/rekap-disposisi?unit_id=1&tanggal_mulai=2026-06-01&tanggal_sampai=2026-06-30
   ```

4. **Arsip Jatuh Tempo**
   ```
   http://localhost/reports/arsip-jatuh-tempo?retensi_status=aktif&bulan_depan=3
   ```

5. **Audit Trail** (Admin only)
   ```
   http://localhost/reports/audit-trail?user_id=1&action=update&tanggal_mulai=2026-06-01&tanggal_sampai=2026-06-21
   ```

6. **Statistik Dashboard**
   ```
   http://localhost/reports/statistik?tahun=2026
   ```

## Responsive Design

Semua halaman menggunakan Bootstrap 5 grid system yang responsif:
- Mobile: 360px+ (col-12)
- Tablet: 768px+ (col-md-*)
- Desktop: 992px+ (col-lg-*)

## Security

- Semua route dilindungi middleware `auth` dan `verified`
- Audit Trail hanya dapat diakses oleh role `admin` dan `admin_tu`
- Export files menggunakan streaming untuk menghindari memory issues

## Customization

### Mengubah Kop Surat PDF
Edit file `resources/views/reports/pdf/buku-agenda.blade.php`:
```html
<h3>{{ strtoupper($instansi) }}</h3>
```

Nilai `$instansi` diambil dari `config('app.name')`.

### Menambah Chart Baru
Edit file `resources/views/reports/statistik/dashboard.blade.php` dan tambahkan canvas element baru, lalu buat Chart.js instance di bagian `@push('scripts')`.

## Troubleshooting

### PDF tidak muncul
```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### Excel export error
```bash
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider"
```

### AuditLog table not found
Buat migration baru:
```bash
php artisan make:migration create_audit_logs_table
```

Isi migration:
```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable();
    $table->string('action');
    $table->string('entity_type');
    $table->unsignedBigInteger('entity_id')->nullable();
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();
});
```

Lalu jalankan:
```bash
php artisan migrate
```
