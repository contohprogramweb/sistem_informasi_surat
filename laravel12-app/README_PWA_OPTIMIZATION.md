# Optimasi Responsive & PWA - SIAP-SMK

## 📋 Ringkasan Implementasi

Modul ini mengimplementasikan Progressive Web App (PWA) dan optimasi responsive untuk SIAP-SMK dengan fokus pada pengalaman mobile-first.

---

## 📁 File yang Dibuat/Diperbarui

### 1. PWA Files
| File | Deskripsi |
|------|-----------|
| `public/manifest.json` | Konfigurasi PWA (nama, icons, theme) |
| `public/sw.js` | Service Worker untuk caching offline |
| `resources/views/errors/offline.blade.php` | Halaman fallback offline |

### 2. Responsive CSS
| File | Deskripsi |
|------|-----------|
| `public/css/responsive-custom.css` | Custom styles untuk mobile (360px+) |

### 3. Security Middleware
| File | Deskripsi |
|------|-----------|
| `app/Http/Middleware/SecurityHeaders.php` | Header keamanan (CSP, X-Frame-Options, dll) |

### 4. Layout Updates
| File | Deskripsi |
|------|-----------|
| `resources/views/layouts/app-bootstrap.blade.php` | Layout utama dengan PWA support |
| `resources/views/errors/layout.blade.php` | Template error pages |
| `resources/views/errors/404.blade.php` | Error page 404 (Bahasa Indonesia) |
| `resources/views/errors/403.blade.php` | Error page 403 (Bahasa Indonesia) |
| `resources/views/errors/500.blade.php` | Error page 500 (Bahasa Indonesia) |
| `resources/views/errors/503.blade.php` | Error page 503 (Bahasa Indonesia) |

---

## ✨ Fitur yang Diimplementasikan

### 1. Responsive Design ✅

#### Mobile-First Breakpoints
- **360px+**: Support untuk smartphone kecil
- **576px**: SM (Small)
- **768px**: MD (Medium)
- **992px**: LG (Large)
- **1200px**: XL (Extra Large)

#### Touch-Friendly Elements
```css
/* Minimum touch target: 44px */
.btn, .form-control, .nav-link {
    min-height: 44px;
    min-width: 44px;
}
```

#### Sidebar Collapsible
- Hamburger menu pada mobile (< 992px)
- Overlay saat sidebar terbuka
- Auto-close saat resize ke desktop

#### DataTables Responsive
- Horizontal scroll pada mobile
- Stack layout untuk filter/pagination
- Center-aligned controls

#### Form Label Stacking
- Label di atas input pada mobile
- Full-width form controls
- Proper spacing

### 2. PWA (Progressive Web App) ✅

#### manifest.json
```json
{
    "name": "SIAP-SMK",
    "display": "standalone",
    "theme_color": "#0d6efd",
    "icons": [72x72 ... 512x512]
}
```

#### Service Worker Strategy
- **Network First** untuk halaman dinamis
- **Cache Fallback** untuk aset statis
- Cache CDN resources (Bootstrap, jQuery, DataTables, Chart.js)
- Offline page fallback

#### Caching Behavior
```javascript
// Install: Cache app shell
// Activate: Delete old caches
// Fetch: Network first → Cache fallback → Offline page
```

### 3. Performance Optimization ✅

#### CDN Resources
```html
<!-- Bootstrap 5 -->
https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/...

<!-- jQuery -->
https://code.jquery.com/jquery-3.7.0.min.js

<!-- DataTables -->
https://cdn.datatables.net/1.13.4/...

<!-- Chart.js -->
https://cdn.jsdelivr.net/npm/chart.js@4.3.0/...
```

#### Lazy Loading Recommendations
```html
<!-- Images -->
<img src="placeholder.jpg" data-src="actual.jpg" loading="lazy">

<!-- PDF Preview -->
<iframe data-src="pdf-url" loading="lazy"></iframe>
```

#### Database Optimization
```php
// Indexes untuk kolom sering dicari
Schema::table('surat_masuk', function (Blueprint $table) {
    $table->index('agenda');
    $table->index('nomor_surat');
    $table->index('perihal');
    $table->index('pengirim');
    $table->index('tanggal_terima');
});

// Eager Loading
SuratMasuk::with(['klasifikasi', 'unit', 'lampiran'])->get();

// Query Caching
$klasifikasi = Cache::remember('klasifikasi_all', 3600, function () {
    return Klasifikasi::all();
});
```

### 4. Security Headers ✅

#### Middleware: SecurityHeaders
```php
// X-Frame-Options: SAMEORIGIN (anti-clickjacking)
// X-Content-Type-Options: nosniff
// Referrer-Policy: strict-origin-when-cross-origin
// Content-Security-Policy: Allow self + CDN
// Strict-Transport-Security: HSTS (HTTPS only)
// Permissions-Policy: Disable geolocation, camera, etc.
```

#### CSP Policy
```
default-src 'self'
script-src 'self' 'unsafe-inline' cdn.jsdelivr.net code.jquery.com ...
style-src 'self' 'unsafe-inline' cdn.jsdelivr.net ...
img-src 'self' data: https:
```

---

## 🚀 Cara Menggunakan

### 1. Daftarkan Middleware

**File:** `app/Http/Kernel.php`

```php
protected $middleware = [
    // ... global middleware
    \App\Http\Middleware\SecurityHeaders::class,
];
```

### 2. Generate Icons (Opsional)

Buat icon PNG berbagai ukuran di folder `public/icons/`:
- icon-72x72.png
- icon-96x96.png
- icon-128x128.png
- icon-144x144.png
- icon-152x152.png
- icon-192x192.png
- icon-384x384.png
- icon-512x512.png

Gunakan tools seperti [realfavicongenerator.net](https://realfavicongenerator.net/)

### 3. Jalankan Migration untuk Indexes

```bash
php artisan make:migration add_performance_indexes

# Edit migration file:
Schema::table('surat_masuk', function (Blueprint $table) {
    $table->index('agenda');
    $table->index('nomor_surat');
    $table->index('perihal');
    $table->index('pengirim');
    $table->index('tanggal_terima');
    $table->fullText(['perihal', 'pengirim', 'nomor_surat']);
});

Schema::table('surat_keluar', function (Blueprint $table) {
    $table->index('agenda');
    $table->index('nomor_surat');
    $table->index('perihal');
    $table->index('tujuan');
    $table->index('tanggal_keluar');
    $table->fullText(['perihal', 'tujuan', 'nomor_surat']);
});
```

### 4. Akses Aplikasi

```
http://localhost/
```

Browser akan otomatis:
- Register Service Worker
- Cache halaman yang dibuka
- Tampilkan badge "Install App" jika supported

---

## 📱 Testing PWA

### Chrome DevTools
1. Buka DevTools (F12)
2. Tab **Application** → **Service Workers**
3. Check status: "Activated and running"
4. Tab **Manifest** → Verify details

### Offline Testing
1. DevTools → **Network** tab
2. Select **Offline**
3. Refresh page
4. Should show cached content or offline page

### Lighthouse Audit
1. DevTools → **Lighthouse** tab
2. Select categories: PWA, Performance, Best Practices
3. Generate report
4. Target score: 90+

---

## 🔧 Troubleshooting

### Service Worker tidak register
```javascript
// Check browser support
if ('serviceWorker' in navigator) {
    console.log('SW supported');
} else {
    console.log('SW NOT supported');
}
```

### Cache tidak update
- Increment CACHE_NAME di `sw.js`: `'siap-smk-v2'`
- Clear cache di DevTools → Application → Storage → Clear site data

### CSP blocking resources
- Check browser console for CSP errors
- Add allowed domains to CSP policy in `SecurityHeaders.php`

---

## 📊 Performance Checklist

- [ ] Images lazy loading
- [ ] PDF preview lazy loading
- [ ] Database indexes created
- [ ] Eager loading implemented
- [ ] Query caching for master data
- [ ] CDN for external libraries
- [ ] Minified CSS/JS in production
- [ ] Gzip/Brotli compression enabled
- [ ] Browser caching headers set

---

## 📖 Referensi

- [Bootstrap 5 Responsive](https://getbootstrap.com/docs/5.3/layout/breakpoints/)
- [PWA Guide](https://web.dev/progressive-web-apps/)
- [Service Worker API](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [Web Performance](https://web.dev/performance/)
- [Security Headers](https://owasp.org/www-project-secure-headers/)

---

**Versi:** 1.0.0  
**Tanggal:** Juni 2026  
**Status:** ✅ Production Ready
