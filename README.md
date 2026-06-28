# SIAP-SMK (Sistem Informasi Arsip & Persuratan)

Aplikasi manajemen surat dan arsip digital berbasis Laravel 12 untuk kebutuhan persuratan instansi.

## 📋 Fitur Utama

- **Manajemen Surat Masuk/Keluar** - Registrasi, tracking, dan disposisi surat
- **Tanda Tangan Elektronik (TTE)** - Workflow persetujuan dan validasi digital
- **Role-Based Access Control (RBAC)** - Admin, Kepala Dinas, Kasubbag, Staff
- **Arsip Digital** - Penyimpanan dan pencarian dokumen PDF
- **Disposisi Multi-level** - Routing surat ke unit tujuan
- **Audit Trail** - Log aktivitas dan riwayat perubahan
- **Export & Import** - Dukungan Excel untuk bulk operations
- **PWA Support** - Progressive Web App untuk akses mobile

## 🚀 Instalasi Cepat

### Prasyarat
- PHP >= 8.2
- Composer
- MySQL/MariaDB 8.0+
- Node.js & NPM

### Langkah Instalasi

```bash
# Clone repository
cd laravel12-app

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Konfigurasi database di file .env
# DB_DATABASE=siap_smk

# Migrate database
php artisan migrate --seed

# Build assets
npm run build

# Jalankan aplikasi
php artisan serve
```

Aplikasi berjalan di `http://localhost:8000`

## 🔐 Default User

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@example.com | password |
| Kepala Dinas | kepala@example.com | password |
| Kasubbag | kasubbag@example.com | password |
| Staff | staff@example.com | password |

## 🧪 Pengujian

```bash
php artisan test
```

## 📁 Struktur Project

```
/workspace
├── laravel12-app/      # Aplikasi Laravel 12
├── basisdata.sql       # Schema database
└── README.md           # Dokumentasi ini
```

## 🛡️ Keamanan

- CSRF Protection
- Rate Limiting
- Password Hashing (bcrypt)
- Authorization Policies
- Input Validation
- Session timeout 30 menit

## 📄 License

MIT License
