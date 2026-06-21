# Modul Tanda Tangan Elektronik (TTE) - SIAP-SMK

## Overview
Modul TTE memungkinkan pimpinan untuk menandatangani surat keluar secara elektronik dengan menggunakan gambar tanda tangan yang telah diupload oleh Admin TU.

## Fitur Utama

### 1. Upload Gambar Tanda Tangan (Admin TU)
- Format file: PNG transparan
- Enkripsi AES-256 menggunakan APP_KEY Laravel
- File disimpan dalam bentuk terenkripsi di database
- Hanya proses TTE yang dapat mendekripsi file

### 2. Halaman TTE (Pimpinan)
- Pratinjau PDF menggunakan PDF.js
- Input posisi X, Y untuk penempatan tanda tangan
- Skala gambar dapat disesuaikan (preset: Kecil, Sedang, Besar)
- Modal konfirmasi sebelum tanda tangan

### 3. Proses TTE
- Dekripsi gambar tanda tangan
- Menggunakan FPDI untuk menyisipkan gambar ke PDF
- Set password owner untuk mencegah modifikasi
- Hitung SHA-256 hash file final
- Update status surat → "Tertandatangani"
- Lampirkan PDF final sebagai lampiran baru

### 4. Error Handling
- Rollback status ke "Siap Tandatangan" jika gagal
- Notifikasi error ke Pimpinan dan TU via log
- Catat error message di tte_logs table

### 5. Log TTE
- user_id: User yang melakukan TTE
- waktu: Timestamp saat TTE dilakukan
- surat_keluar_id: ID surat yang ditandatangani
- hash_file: SHA-256 hash dari file final
- position_x, position_y: Posisi tanda tangan
- scale: Skala gambar yang digunakan
- ip_address: IP address user
- success: Status keberhasilan

## Instalasi

### 1. Jalankan Migration
```bash
php artisan migrate
```

### 2. Install Dependency FPDI (Optional)
```bash
composer require setasign/fpdi setasign/fpdi-protection
```

### 3. Setup Permissions
Pastikan user memiliki permission berikut:
- `tte.upload` - Untuk upload tanda tangan (Admin TU)
- `surat_keluar.ttd` - Untuk melakukan TTE (Pimpinan)
- `tte.verify` - Untuk verifikasi hash
- `tte.manage` - Untuk manage tanda tangan (Admin)

## Struktur File

```
app/
├── Models/
│   ├── TteSignature.php      # Model signature user
│   ├── TteLog.php            # Model log TTE
│   └── Lampiran.php          # Model lampiran (updated)
├── Services/
│   └── TteSignatureService.php  # Service utama TTE
└── Http/Controllers/
    └── TteSignatureController.php  # Controller TTE

database/migrations/
├── 2026_06_21_030000_create_tte_signatures_table.php
├── 2026_06_21_030001_create_tte_logs_table.php
└── 2026_06_21_030002_add_tte_fields_to_surat_keluar_table.php

resources/views/tte/
├── upload-signature.blade.php  # View upload tanda tangan
├── sign.blade.php              # View halaman TTE
└── logs.blade.php              # View log TTE

routes/
└── web.php  # Routes TTE (ditambahkan di akhir file)
```

## Routes

| Method | URI | Name | Permission |
|--------|-----|------|------------|
| GET | `/tte/upload-signature` | `tte.upload-signature` | `tte.upload` |
| POST | `/tte/upload-signature` | `tte.upload-signature.store` | `tte.upload` |
| GET | `/tte/sign/{suratKeluar}` | `tte.sign-page` | `surat_keluar.ttd` |
| POST | `/tte/sign/{suratKeluar}` | `tte.sign-document` | `surat_keluar.ttd` |
| GET | `/tte/pdf-preview/{suratKeluar}` | `tte.pdf-preview` | `surat_keluar.ttd` |
| GET | `/tte/logs/{suratKeluar}` | `tte.logs` | `surat_keluar.view.all` |
| POST | `/tte/verify-hash` | `tte.verify-hash` | `tte.verify` |
| DELETE | `/tte/signature/{id}` | `tte.signature.delete` | `tte.manage` |

## Penggunaan

### Upload Tanda Tangan (Admin TU)
1. Buka halaman `/tte/upload-signature`
2. Pilih file PNG transparan
3. Klik "Upload & Enkripsi"

### Tanda Tangan Surat (Pimpinan)
1. Buka surat dengan status "Siap Tandatangan"
2. Klik tombol "Tanda Tangan"
3. Atur posisi X, Y dan skala
4. Klik "Tandatangan"
5. Konfirmasi pada modal
6. Surat akan berubah status menjadi "Tertandatangann"

### Verifikasi Hash
Gunakan endpoint `/tte/verify-hash` dengan parameter:
- `pdf_path`: Path file PDF
- `expected_hash`: Hash SHA-256 yang diharapkan

## Keamanan

1. **Enkripsi File**: File tanda tangan dienkripsi menggunakan AES-256
2. **Hash Verification**: Setiap file PDF final memiliki hash SHA-256
3. **Password Protection**: PDF final diproteksi dengan password owner
4. **Access Control**: Permission-based access untuk setiap fitur
5. **Audit Trail**: Semua aktivitas TTE dicatat di database

## Troubleshooting

### Error: "Library FPDI tidak terinstall"
Install library FPDI:
```bash
composer require setasign/fpdi
```

### Error: "File tanda tangan tidak ditemukan"
- Pastikan Admin TU sudah upload tanda tangan
- Cek tabel `tte_signatures` untuk signature aktif

### Error: "PDF surat tidak ditemukan"
- Pastikan surat memiliki lampiran PDF
- Cek tabel `lampiran` untuk file PDF

## Catatan Penting

1. File temporary signature akan dihapus otomatis setelah 1 jam
2. Signature lama akan dinonaktifkan saat upload signature baru
3. Status surat akan rollback otomatis jika TTE gagal
4. Hash file disimpan untuk verifikasi integritas dokumen
