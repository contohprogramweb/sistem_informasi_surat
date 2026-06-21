# Dashboard Personal SIAP-SMK dengan Bootstrap 5

## Ringkasan Implementasi

Dashboard personal yang responsif untuk Sistem Arsip dan Persuratan SMK (SIAP-SMK) dengan dukungan role-based view untuk Pimpinan, Kabag, dan Staff TU.

## Fitur Utama

### 1. Pimpinan Dashboard
- **Widget "Surat Masuk Perlu Disposisi"**: Menampilkan count + daftar 5 surat teratas yang perlu disposisi
- **Widget "Disposisi Saya yang Berjalan"**: Tracking status disposisi dengan indikator overdue
- **Widget "Persetujuan Surat Keluar"**: Menunggu approve/ttd
- **Indikator Overdue**: Count merah untuk disposisi yang melewati batas waktu
- **Filter Cepat**: Prioritas Tinggi/Segera, Batas Waktu Hari Ini, Overdue

### 2. Kabag Dashboard
- **Widget "Disposisi Masuk"**: Disposisi yang belum selesai
- **Widget "Disposisi yang Saya Teruskan"**: Status tracking disposisi yang diteruskan ke bawahan
- **Widget "Surat Keluar Unit"**: Draft dan review surat keluar dari unit

### 3. Staff TU Dashboard
- **Statistik Cards**: 
  - Surat masuk hari ini
  - Surat keluar hari ini
  - Arsip jatuh tempo
  - Disposisi terbuka
- **Quick Action Buttons**: Input Surat Masuk, Buat Surat Keluar, Kelola Arsip
- **Grafik Chart.js**: Statistik surat masuk/keluar per bulan (6 bulan terakhir)

### 4. Responsive Design
- Grid layout Bootstrap 5 yang menyesuaikan mobile (360px+)
- Mobile-first approach dengan breakpoints: 360px, 576px, 768px, 992px, 1200px

### 5. Real-time Update (Opsional)
- Polling AJAX setiap 60 detik untuk badge notifikasi
- API endpoint `/api/dashboard/notification-counts`

## Struktur File

```
laravel12-app/
├── app/
│   └── Http/
│       └── Controllers/
│           └── DashboardController.php      # Controller utama dashboard
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app-bootstrap.blade.php      # Layout Bootstrap 5
│       │   └── navigation-bootstrap.blade.php # Navbar Bootstrap
│       └── dashboard/
│           ├── pimpinan.blade.php           # View dashboard Pimpinan
│           ├── kabag.blade.php              # View dashboard Kabag
│           └── staff-tu.blade.php           # View dashboard Staff TU
├── public/
│   └── css/
│       └── dashboard-custom.css             # Custom CSS styling
└── routes/
    └── web.php                              # Route definitions
```

## Instalasi & Konfigurasi

### 1. Tambahkan Route
Route sudah ditambahkan di `routes/web.php`:
```php
// Dashboard dengan role-based view
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// API endpoint untuk real-time notification counts
Route::get('/api/dashboard/notification-counts', [DashboardController::class, 'getNotificationCounts'])
    ->middleware(['auth', 'verified'])
    ->name('api.dashboard.notification-counts');
```

### 2. Permission/Gates yang Dibutuhkan
Dashboard menggunakan permission berikut untuk menentukan role user:

**Pimpinan:**
- `disposisi.massal` atau `surat_masuk.disposisi`

**Kabag:**
- `disposisi.forward` dan `surat_keluar.review`

**Staff TU:**
- `surat_masuk.create` atau `arsip.manage`

### 3. Model Relationships
Pastikan model-model berikut memiliki relasi yang benar:
- `User` → `Unit` (many-to-many atau one-to-many)
- `SuratMasuk` → `Disposisi` (one-to-many)
- `Disposisi` → `User` (dari_user_id, ke_user_id)
- `SuratKeluar` → `Unit` (unit_pembuat_id)

## Cara Penggunaan

### Akses Dashboard
Setelah login, user dapat mengakses dashboard melalui:
- URL: `/dashboard`
- Menu navigasi: klik "Dashboard"

### Role Detection
Role user ditentukan secara otomatis berdasarkan permission yang dimiliki:
```php
// Di DashboardController::determineUserRole()
if ($user->can('disposisi.massal')) {
    return 'pimpinan';
}
```

### Widget Features

#### Widget Pimpinan
1. **Surat Perlu Disposisi**
   - Link ke detail surat
   - Badge prioritas (Segera, Tinggi, Normal, Rendah)
   - Filter "Lihat Semua" ke halaman surat masuk

2. **Disposisi Berjalan**
   - Indikator visual untuk overdue (background merah)
   - Countdown batas waktu
   - Status badge berwarna

3. **Persetujuan Surat Keluar**
   - Status workflow (Draft, Review, Disetujui, Siap TTD)
   - Informasi unit pembuat

#### Widget Kabag
1. **Disposisi Masuk**
   - Daftar disposisi dari pimpinan
   - Status tracking

2. **Disposisi Diteruskan**
   - Tracking disposisi yang diteruskan ke staf
   - Status dari penerima

3. **Surat Keluar Unit**
   - Draft dan review dari unit sendiri

#### Widget Staff TU
1. **Statistik Cards**
   - Angka real-time
   - Icon visual

2. **Quick Actions**
   - Tombol besar dengan icon
   - Link langsung ke form

3. **Chart.js Graph**
   - Bar chart interaktif
   - Data 6 bulan terakhir
   - Tooltip informatif

## Custom Styling

### CSS Variables
```css
:root {
    --dashboard-primary: #0d6efd;
    --dashboard-success: #198754;
    --dashboard-warning: #ffc107;
    --dashboard-danger: #dc3545;
    --dashboard-info: #0dcaf0;
}
```

### Responsive Breakpoints
- 360px: Extra small (portrait phones)
- 576px: Small (landscape phones)
- 768px: Medium (tablets)
- 992px: Large (desktops)
- 1200px: Extra large (large desktops)

## Real-time Updates

### AJAX Polling
```javascript
setInterval(function() {
    fetch('/api/dashboard/notification-counts')
        .then(response => response.json())
        .then(data => {
            // Update badge counts
        });
}, 60000); // 60 detik
```

### API Response Format
```json
{
    "surat_perlu_disposisi": 5,
    "disposisi_overdue": 2,
    "disposisi_masuk": 3
}
```

## Testing

### Manual Testing Checklist
- [ ] Login sebagai Pimpinan → verifikasi widget tampil
- [ ] Login sebagai Kabag → verifikasi widget tampil
- [ ] Login sebagai Staff TU → verifikasi statistik dan chart
- [ ] Test responsive di berbagai ukuran layar (360px, 768px, 1024px)
- [ ] Test filter cepat (Prioritas, Batas Waktu, Overdue)
- [ ] Test real-time update (tunggu 60 detik, cek console log)
- [ ] Test link navigasi ke halaman terkait
- [ ] Test empty state (tidak ada data)

### Browser Support
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Troubleshooting

### Widget tidak menampilkan data
- Cek relasi model di `SuratMasuk`, `Disposisi`, `SuratKeluar`
- Pastikan permission user sudah diatur dengan benar
- Cek query di controller untuk error

### Chart tidak muncul
- Pastikan Chart.js CDN loaded
- Cek console browser untuk JavaScript errors
- Verifikasi format data `$chartData` dari controller

### Responsive tidak bekerja
- Clear browser cache
- Pastikan viewport meta tag ada di layout
- Test di browser DevTools dengan berbagai ukuran

## Pengembangan Selanjutnya

### Fitur yang Dapat Ditambahkan
1. **Drag & Drop Widgets**: User dapat mengatur layout dashboard
2. **Export Dashboard**: PDF/Excel report dari statistik
3. **Dark Mode Toggle**: Switch manual untuk dark theme
4. **Widget Settings**: User dapat memilih widget yang ditampilkan
5. **Real-time WebSocket**: Push notification untuk disposisi baru
6. **Activity Timeline**: Feed aktivitas terbaru
7. **Calendar Widget**: Jadwal jatuh tempo arsip

### Optimisasi
1. **Query Caching**: Cache hasil query untuk performa
2. **Lazy Loading**: Load widget secara asynchronous
3. **Pagination**: Untuk list yang panjang
4. **Search**: Pencarian cepat di dashboard

## License

Proprietary - SIAP-SMK System

## Contact

Untuk pertanyaan atau bug report, hubungi tim pengembangan SIAP-SMK.
