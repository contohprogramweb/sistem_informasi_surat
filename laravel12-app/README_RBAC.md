# SIAP-SMK - Sistem Informasi Arsip Surat Masuk dan Keluar

## Implementasi Autentikasi dan RBAC (Role-Based Access Control)

Dokumen ini menjelaskan implementasi sistem keamanan untuk aplikasi SIAP-SMK menggunakan Laravel 12.

---

## 📋 Fitur Keamanan yang Diimplementasikan

### 1. Session Timeout (30 Menit Idle)

**File:** `config/session.php`
```php
'lifetime' => (int) env('SESSION_LIFETIME', 30), // 30 menit
```

**Konfigurasi .env:**
```env
SESSION_DRIVER=database
SESSION_LIFETIME=30
```

Session akan otomatis expire setelah 30 menit tidak ada aktivitas.

---

### 2. Rate Limiting Login

**File:** `app/Providers/AppServiceProvider.php`
```php
RateLimiter::for('login', function (Request $request) {
    return RateLimiter::limit($request->input('email'), 3)
        ->response(function () {
            return response()->json([
                'message' => 'Terlalu banyak percobaan login. Silakan coba lagi dalam 15 menit.'
            ], 429);
        });
});
```

**File:** `routes/auth.php`
```php
Route::post('login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('throttle:login');
```

- Maksimal **3 percobaan login gagal** per **15 menit**
- Berdasarkan email yang digunakan

---

### 3. Password Policy (Strong Password)

**File:** `app/Rules/StrongPassword.php`

Policy password yang diterapkan:
- ✅ Minimal **12 karakter**
- ✅ Harus ada **huruf kapital** (A-Z)
- ✅ Harus ada **huruf kecil** (a-z)
- ✅ Harus ada **angka** (0-9)
- ✅ Harus ada **simbol khusus** (@$!%*?&#)
- ✅ **Tidak boleh mengandung username/email**

**Penggunaan di PasswordController:**
```php
use App\Rules\StrongPassword;

'password' => [
    'required', 
    'confirmed', 
    new StrongPassword(),
],
```

---

### 4. Spatie Permission - Roles & Permissions

#### Roles yang Didefinisikan

| Role | Deskripsi |
|------|-----------|
| `pimpinan` | Kepala sekolah/wakil kepala sekolah |
| `kabag` | Kepala bagian/unit kerja |
| `staff_tu` | Staff tata usaha |

#### Permissions yang Didefinisikan

**Surat Masuk:**
- `surat_masuk.view.all` - Lihat semua surat masuk
- `surat_masuk.view.unit` - Lihat surat masuk unit sendiri
- `surat_masuk.create` - Buat surat masuk baru
- `surat_masuk.edit` - Edit/hapus surat masuk

**Surat Keluar:**
- `surat_keluar.create` - Buat surat keluar draft
- `surat_keluar.review` - Review surat keluar (Kabag)
- `surat_keluar.approve` - Approve surat keluar (Pimpinan)
- `surat_keluar.ttd` - TTD elektronik surat keluar

**Disposisi:**
- `disposisi.create` - Buat disposisi baru
- `disposisi.receive` - Terima disposisi
- `disposisi.forward` - Teruskan disposisi
- `disposisi.massal` - Disposisi massal (Pimpinan)

**Master Data & Admin:**
- `master_data.manage` - Kelola master data
- `user.manage` - Kelola user
- `laporan.view` - Lihat laporan
- `arsip.manage` - Kelola arsip

**Fitur Lanjutan:**
- `tte.execute` - Eksekusi TTE
- `import.execute` - Import Excel
- `audit.view` - Lihat audit log

**File Seeder:** `database/seeders/RolePermissionSeeder.php`

---

### 5. Middleware untuk Role & Permission

**File:** `bootstrap/app.php`
```php
$middleware->alias([
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
    'role.check' => \App\Http\Middleware\RoleMiddleware::class,
]);
```

**Custom Middleware:** `app/Http/Middleware/RoleMiddleware.php`

Middleware custom untuk cek multiple roles:
```php
Route::middleware(['role:pimpinan,kabag'])->group(...)
```

---

### 6. Gate untuk Unit-Based Access

**File:** `app/Providers/AppServiceProvider.php`

```php
// Kabag hanya bisa lihat surat dari unit-nya sendiri
Gate::define('view-surat-unit', function (User $user, $surat) {
    if ($user->can('surat_masuk.view.all')) {
        return true;
    }
    
    if ($surat instanceof SuratMasuk) {
        return $user->unit_id === $surat->unit_id;
    }
    
    return false;
});
```

**Gate lainnya:**
- `receive-disposisi` - Cek apakah user bisa menerima disposisi
- `approve-surat-keluar` - Cek approval surat keluar berdasarkan unit

---

## 🛣️ Contoh Penerapan pada Routes

**File:** `routes/web.php`

### Surat Masuk
```php
// View dengan permission minimal
Route::get('/surat-masuk', [SuratMasukController::class, 'index'])
    ->name('surat-masuk.index')
    ->can('surat_masuk.view.unit');

// Detail dengan Gate check (unit-based access)
Route::get('/surat-masuk/{suratMasuk}', [SuratMasukController::class, 'show'])
    ->name('surat-masuk.show')
    ->can('view-surat-unit', 'suratMasuk');

// Create dengan permission
Route::middleware(['permission:surat_masuk.create'])->group(function () {
    Route::get('/surat-masuk/create', ...)->name('surat-masuk.create');
    Route::post('/surat-masuk', ...)->name('surat-masuk.store');
});
```

### Surat Keluar
```php
// Review (Kabag)
Route::middleware(['permission:surat_keluar.review'])
    ->post('/surat-keluar/{id}/review', ...)
    ->name('surat-keluar.review');

// Approve (Pimpinan)
Route::middleware(['permission:surat_keluar.approve'])
    ->post('/surat-keluar/{id}/approve', ...)
    ->name('surat-keluar.approve');
```

### Disposisi
```php
// Disposisi massal (hanya Pimpinan)
Route::middleware(['permission:disposisi.massal'])
    ->post('/disposisi/massal', ...)
    ->name('disposisi.massal');
```

### Master Data
```php
Route::middleware(['permission:master_data.manage'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('units', UnitController::class);
        Route::resource('klasifikasi', KlasifikasiArsipController::class);
    });
```

---

## 📝 Cara Penggunaan di Controller

### Cek Permission
```php
if ($user->can('surat_masuk.create')) {
    // User bisa create surat masuk
}

// Atau menggunakan middleware
public function __construct()
{
    $this->middleware(['permission:surat_masuk.edit']);
}
```

### Cek Role
```php
if ($user->hasRole('pimpinan')) {
    // User adalah pimpinan
}

// Multiple roles
if ($user->hasAnyRole(['pimpinan', 'kabag'])) {
    // User adalah pimpinan ATAU kabag
}
```

### Menggunakan Gate
```php
// Di Controller
if (Gate::allows('view-surat-unit', $surat)) {
    // User bisa akses surat ini
}

// Atau helper function
if (auth()->user()->can('view-surat-unit', $surat)) {
    // ...
}
```

---

## 🔧 Konfigurasi Database

**File:** `.env.example`
```env
# Database MySQL 8.0
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siap_smk
DB_USERNAME=root
DB_PASSWORD=

# Queue menggunakan database driver
QUEUE_CONNECTION=database

# Session dengan timeout 30 menit
SESSION_DRIVER=database
SESSION_LIFETIME=30
```

---

## 🚀 Cara Menjalankan Seeder

```bash
# Jalankan seeder untuk roles & permissions
php artisan db:seed --class=RolePermissionSeeder

# Atau seed semua
php artisan db:seed
```

---

## 📂 Struktur File yang Dibuat/Diubah

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Auth/
│   │       └── PasswordController.php          ✓ Diubah
│   └── Middleware/
│       └── RoleMiddleware.php                  ✓ Baru
├── Providers/
│   └── AppServiceProvider.php                  ✓ Diubah
└── Rules/
    └── StrongPassword.php                      ✓ Baru

bootstrap/
└── app.php                                     ✓ Diubah

config/
└── session.php                                 ✓ Diubah

database/
└── seeders/
    └── RolePermissionSeeder.php                ✓ Diubah

routes/
├── web.php                                     ✓ Diubah
└── auth.php                                    ✓ Diubah

.env.example                                    ✓ Diubah
```

---

## ✅ Checklist Implementasi

- [x] Session timeout 30 menit idle
- [x] Rate limiting login (3x/15 menit)
- [x] Password policy kuat (12 char, kombinasi, no username)
- [x] Spatie Permission roles (pimpinan, kabag, staff_tu)
- [x] Spatie Permission permissions (semua permission sesuai spec)
- [x] Middleware role & permission
- [x] Gate untuk unit-based access
- [x] Contoh penerapan routes dengan RBAC
- [x] Konfigurasi .env.example lengkap

---

## 🔐 Best Practices Keamanan

1. **Selalu gunakan middleware** pada route yang membutuhkan autentikasi
2. **Gunakan Gate** untuk validasi akses berbasis data (unit-based)
3. **Validasi password** di registration dan update password
4. **Log semua aktivitas** penting ke audit_logs table
5. **Gunakan HTTPS** di production (set SESSION_SECURE_COOKIE=true)
6. **Rotate APP_KEY** secara berkala
7. **Backup database** secara rutin

---

## 📞 Support

Untuk pertanyaan mengenai implementasi keamanan ini, silakan hubungi tim developer SIAP-SMK.
