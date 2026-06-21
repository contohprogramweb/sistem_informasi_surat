# Fitur Pencarian Global SIAP-SMK

## Overview
Implementasi fitur pencarian global dengan kemampuan full-text search, filter lanjutan, sorting, dan export untuk aplikasi SIAP-SMK.

## Files Created

### 1. Service Layer
- **`app/Services/SearchService.php`**
  - Full-text search menggunakan MySQL MATCH/AGAINST
  - Fallback ke LIKE search jika FULLTEXT index tidak tersedia
  - Support boolean mode operators: `+`, `-`, `*`, `"phrase"`
  - Highlighting keyword pada hasil pencarian
  - Sorting by: relevance, tanggal, prioritas, agenda

### 2. Controller
- **`app/Http/Controllers/SearchController.php`**
  - `index()` - Halaman pencarian dengan filters
  - `search()` - API endpoint untuk AJAX search
  - `export()` - Export hasil ke Excel/CSV/PDF
  - `getFilterOptions()` - Load filter options via AJAX

### 3. Views
- **`resources/views/search/index.blade.php`**
  - Search box di navbar (quick search)
  - Advanced filters (collapsible)
  - DataTables integration untuk hasil
  - Export buttons (Excel, CSV, PDF)
  
- **`resources/views/search/export-pdf.blade.php`**
  - Template PDF untuk export

### 4. Migration
- **`database/migrations/2026_06_21_050000_add_fulltext_indexes_to_surat_tables.php`**
  - Menambahkan FULLTEXT index pada tabel `surat_masuk` dan `surat_keluar`

### 5. Routes
- **`routes/web.php`**
  ```php
  GET  /search              - Halaman pencarian
  GET  /search/api          - API search (AJAX)
  GET  /search/export       - Export results
  GET  /search/filter-options - Filter options
  ```

### 6. Navigation Update
- **`resources/views/layouts/navigation-bootstrap.blade.php`**
  - Added "Pencarian" menu item
  - Added quick search form in navbar

## Features

### 1. Pencarian Global (Navbar)
**Searchable Fields:**
- Surat Masuk: agenda, nomor_surat, perihal, pengirim, indeks/kata_kunci
- Surat Keluar: nomor_surat_final, perihal, tujuan, isi_ringkas

**Teknologi:**
- MySQL FULLTEXT dengan MATCH/AGAINST (Boolean Mode)
- Fallback ke LIKE search jika FULLTEXT tidak tersedia
- Highlight keyword pada hasil

### 2. Filter Lanjutan
**Filters tersedia:**
- ✅ Jenis surat (masuk/keluar/all)
- ✅ Status (multi-select dropdown)
- ✅ Rentang tanggal (date range picker)
- ✅ Klasifikasi (single select)
- ✅ Unit (multi-select)
- ✅ Sifat surat (multi-select)
- ✅ Prioritas (multi-select: Rendah, Normal, Tinggi, Segera)

### 3. Sort & Pagination
**Sort Options:**
- Relevansi (default)
- Tanggal (terbaru/terlama)
- Prioritas (Segera → Rendah)
- Agenda

**Pagination:**
- Server-side processing dengan DataTables
- Page size: 10, 25, 50, 100
- AJAX loading untuk performa optimal

### 4. Export Hasil
**Format Export:**
- ✅ Excel (.xlsx) - requires maatwebsite/excel
- ✅ CSV (.csv) - native PHP fputcsv
- ✅ PDF (.pdf) - HTML to PDF conversion

**Export Current View:**
- Export sesuai filter yang aktif
- Include highlighted keywords

## Installation

### 1. Run Migration
```bash
cd /workspace/laravel12-app
php artisan migrate
```

### 2. Verify FULLTEXT Indexes
```sql
-- Check indexes on surat_masuk
SHOW INDEX FROM surat_masuk WHERE Key_name = 'ft_surat_masuk_search';

-- Check indexes on surat_keluar
SHOW INDEX FROM surat_keluar WHERE Key_name = 'ft_surat_keluar_search';
```

### 3. Optional: Install Excel Package
```bash
composer require maatwebsite/excel
```

## Usage

### Basic Search
1. Ketik keyword di search box navbar atau halaman pencarian
2. Tekan Enter atau klik tombol cari
3. Hasil ditampilkan grouped by jenis (Masuk/Keluar)

### Advanced Filters
1. Klik tombol "Filter Lanjutan"
2. Pilih filter yang diinginkan (multi-select supported)
3. Klik "Terapkan Filter"
4. Reset filter dengan tombol "Reset Filter"

### Export Results
1. Lakukan pencarian dengan filter yang diinginkan
2. Klik tombol Excel/CSV/PDF di bagian atas hasil
3. File akan di-download otomatis

### Search Tips
- Gunakan `+` untuk required words: `+rapat +urgent`
- Gunakan `*` untuk wildcard: `rapat*`
- Gunakan quotes untuk exact phrase: `"rapat koordinasi"`
- Gunakan `-` untuk exclude: `rapat -internal`

## API Endpoints

### GET /search/api
**Parameters:**
```
q           string   - Search query
type        string   - all|masuk|keluar
sort_by     string   - relevance|tanggal|prioritas|agenda
sort_order  string   - asc|desc
status      array    - Status filter
klasifikasi_id integer - Klasifikasi filter
unit_id     array    - Unit filter (multi)
sifat_id    array    - Sifat filter (multi)
prioritas   array    - Prioritas filter (multi)
tanggal_mulai date   - Start date
tanggal_sampai date  - End date
```

**Response:**
```json
{
  "success": true,
  "data": [...],
  "total": 25,
  "query": "rapat"
}
```

## Responsive Design
- ✅ Mobile-first approach (360px+)
- ✅ Collapsible filters on mobile
- ✅ Responsive DataTables
- ✅ Touch-friendly buttons and inputs

## Performance Optimization
- AJAX-based search (no page reload)
- Server-side pagination
- Indexed database queries
- Lazy loading for filter options

## Troubleshooting

### FULLTEXT search not working
1. Pastikan database menggunakan MySQL (bukan SQLite/PostgreSQL)
2. Pastikan engine tabel adalah MyISAM atau InnoDB (MySQL 5.6+)
3. Jalankan migration untuk menambahkan FULLTEXT index
4. Cek log Laravel untuk error details

### Export tidak berfungsi
1. Untuk Excel: install `composer require maatwebsite/excel`
2. Untuk PDF: pastikan response headers benar
3. Cek permission folder downloads

### Search lambat
1. Pastikan FULLTEXT index sudah dibuat
2. Gunakan specific keywords (lebih dari 3 karakter)
3. Avoid terlalu banyak wildcards (*)
4. Pertimbangkan caching untuk frequently searched terms

## Future Enhancements
- [ ] Elasticsearch integration untuk large datasets
- [ ] Search suggestions/autocomplete
- [ ] Saved searches
- [ ] Search history
- [ ] Advanced analytics (most searched terms)
- [ ] Multi-language support
