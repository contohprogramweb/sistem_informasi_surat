@extends('layouts.app-bootstrap')

@section('title', 'Pencarian Global')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<link rel="stylesheet" href="{{ asset('css/dashboard-custom.css') }}">
<style>
    .search-container {
        background: #fff;
        border-radius: 8px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-bottom: 24px;
    }
    
    .search-input-group {
        position: relative;
    }
    
    .search-input-group .form-control {
        padding-right: 50px;
        font-size: 16px;
    }
    
    .search-input-group .btn-search {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #6c757d;
        cursor: pointer;
        padding: 8px;
    }
    
    .filter-section {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        margin-top: 20px;
    }
    
    .filter-section .card {
        border: none;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .results-table {
        background: #fff;
        border-radius: 8px;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    mark.bg-warning {
        background-color: #ffc107 !important;
        padding: 2px 4px;
        border-radius: 3px;
    }
    
    .jenis-badge {
        min-width: 80px;
        display: inline-block;
    }
    
    @media (max-width: 768px) {
        .search-container {
            padding: 16px;
        }
        
        .filter-section {
            padding: 16px;
        }
        
        .table-responsive {
            font-size: 14px;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3">
                <i class="bi bi-search me-2"></i>Pencarian Global
            </h2>
            <p class="text-muted">Cari surat masuk dan surat keluar berdasarkan berbagai kriteria</p>
        </div>
    </div>

    <!-- Search Box -->
    <div class="row">
        <div class="col-12">
            <div class="search-container">
                <form id="searchForm" method="GET">
                    <div class="search-input-group mb-3">
                        <input type="text" 
                               class="form-control form-control-lg" 
                               id="searchQuery" 
                               name="q" 
                               placeholder="Cari berdasarkan agenda, nomor surat, perihal, pengirim/tujuan, atau kata kunci..."
                               value="{{ request('q', '') }}"
                               autocomplete="off">
                        <button type="submit" class="btn-search">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Jenis Surat</label>
                            <select class="form-select" id="typeFilter" name="type">
                                <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>Semua</option>
                                <option value="masuk" {{ request('type') == 'masuk' ? 'selected' : '' }}>Surat Masuk</option>
                                <option value="keluar" {{ request('type') == 'keluar' ? 'selected' : '' }}>Surat Keluar</option>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Urut Berdasarkan</label>
                            <select class="form-select" id="sortBy" name="sort_by">
                                <option value="relevance" {{ request('sort_by') == 'relevance' ? 'selected' : '' }}>Relevansi</option>
                                <option value="tanggal" {{ request('sort_by') == 'tanggal' ? 'selected' : '' }}>Tanggal</option>
                                <option value="prioritas" {{ request('sort_by') == 'prioritas' ? 'selected' : '' }}>Prioritas</option>
                                <option value="agenda" {{ request('sort_by') == 'agenda' ? 'selected' : '' }}>Agenda</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label">Urutan</label>
                            <select class="form-select" id="sortOrder" name="sort_order">
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Turun</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Naik</option>
                            </select>
                        </div>
                        
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-primary me-2" id="toggleFilters">
                                <i class="bi bi-funnel me-1"></i>Filter Lanjutan
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i>Cari
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Advanced Filters -->
                <div class="filter-section" id="advancedFilters" style="display: none;">
                    <form id="filterForm">
                        <div class="row g-3">
                            <!-- Status Filter -->
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select select2-multiple" id="statusFilter" name="status[]" multiple="multiple">
                                    <optgroup label="Surat Masuk">
                                        @foreach($statusMasukOptions as $status)
                                            <option value="{{ $status }}" {{ in_array($status, request('status', [])) ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                    <optgroup label="Surat Keluar">
                                        @foreach($statusKeluarOptions as $status)
                                            <option value="{{ $status }}" {{ in_array($status, request('status', [])) ? 'selected' : '' }}>
                                                {{ $status }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            <!-- Klasifikasi -->
                            <div class="col-md-6">
                                <label class="form-label">Klasifikasi</label>
                                <select class="form-select select2-single" id="klasifikasiFilter" name="klasifikasi_id">
                                    <option value="">Semua Klasifikasi</option>
                                    @foreach($klasifikasis as $klasifikasi)
                                        <option value="{{ $klasifikasi->id }}" {{ request('klasifikasi_id') == $klasifikasi->id ? 'selected' : '' }}>
                                            {{ $klasifikasi->kode }} - {{ $klasifikasi->nama_klasifikasi }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Unit -->
                            <div class="col-md-6">
                                <label class="form-label">Unit</label>
                                <select class="form-select select2-multiple" id="unitFilter" name="unit_id[]" multiple="multiple">
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ in_array($unit->id, request('unit_id', [])) ? 'selected' : '' }}>
                                            {{ $unit->nama_unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sifat Surat -->
                            <div class="col-md-6">
                                <label class="form-label">Sifat Surat</label>
                                <select class="form-select select2-multiple" id="sifatFilter" name="sifat_id[]" multiple="multiple">
                                    @foreach($sifatSurats as $sifat)
                                        <option value="{{ $sifat->id }}" {{ in_array($sifat->id, request('sifat_id', [])) ? 'selected' : '' }}>
                                            {{ $sifat->nama_sifat }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Prioritas -->
                            <div class="col-md-6">
                                <label class="form-label">Prioritas</label>
                                <select class="form-select select2-multiple" id="prioritasFilter" name="prioritas[]" multiple="multiple">
                                    @foreach($prioritasOptions as $prioritas)
                                        <option value="{{ $prioritas }}" {{ in_array($prioritas, request('prioritas', [])) ? 'selected' : '' }}>
                                            {{ $prioritas }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Date Range -->
                            <div class="col-md-6">
                                <label class="form-label">Rentang Tanggal</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" id="tanggalMulai" name="tanggal_mulai" 
                                           value="{{ request('tanggal_mulai') }}" placeholder="Dari">
                                    <span class="input-group-text">-</span>
                                    <input type="date" class="form-control" id="tanggalSampai" name="tanggal_sampai" 
                                           value="{{ request('tanggal_sampai') }}" placeholder="Sampai">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-secondary me-2" id="resetFilters">
                                    <i class="bi bi-x-circle me-1"></i>Reset Filter
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle me-1"></i>Terapkan Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="row">
        <div class="col-12">
            <div class="results-table">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Hasil Pencarian</h5>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-success" id="exportExcel">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="exportCsv">
                            <i class="bi bi-file-earmark-spreadsheet me-1"></i>CSV
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger" id="exportPdf">
                            <i class="bi bi-file-earmark-pdf me-1"></i>PDF
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover" id="searchResultsTable">
                        <thead>
                            <tr>
                                <th>Jenis</th>
                                <th>Agenda</th>
                                <th>Nomor Surat</th>
                                <th>Perihal</th>
                                <th>Pengirim/Tujuan</th>
                                <th>Tanggal</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Klasifikasi</th>
                                <th>Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2-single').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#advancedFilters'),
        width: '100%'
    });

    $('.select2-multiple').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#advancedFilters'),
        width: '100%',
        placeholder: 'Pilih (bisa lebih dari satu)',
        allowClear: true
    });

    // Toggle advanced filters
    $('#toggleFilters').click(function() {
        $('#advancedFilters').slideToggle();
    });

    // Initialize DataTable
    var table = $('#searchResultsTable').DataTable({
        processing: true,
        serverSide: false,
        data: [],
        columns: [
            { 
                data: 'jenis',
                render: function(data) {
                    if (data === 'masuk') {
                        return '<span class="badge bg-primary jenis-badge">Masuk</span>';
                    } else {
                        return '<span class="badge bg-success jenis-badge">Keluar</span>';
                    }
                }
            },
            { data: 'agenda' },
            { data: 'nomor_surat' },
            { data: 'perihal' },
            { data: 'pengirim_tujuan' },
            { 
                data: 'tanggal',
                render: function(data) {
                    if (!data) return '-';
                    return new Date(data).toLocaleDateString('id-ID');
                }
            },
            { 
                data: 'prioritas',
                render: function(data) {
                    let badgeClass = 'bg-secondary';
                    if (data === 'Segera') badgeClass = 'bg-danger';
                    else if (data === 'Tinggi') badgeClass = 'bg-warning text-dark';
                    else if (data === 'Normal') badgeClass = 'bg-info text-dark';
                    else if (data === 'Rendah') badgeClass = 'bg-secondary';
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            { data: 'status' },
            { data: 'klasifikasi' },
            { data: 'unit' }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/id.json'
        },
        responsive: true,
        order: [[5, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100]
    });

    // Search form submission
    $('#searchForm').submit(function(e) {
        e.preventDefault();
        performSearch();
    });

    // Filter form submission
    $('#filterForm').submit(function(e) {
        e.preventDefault();
        performSearch();
    });

    // Reset filters
    $('#resetFilters').click(function() {
        $('#statusFilter').val(null).trigger('change');
        $('#klasifikasiFilter').val('').trigger('change');
        $('#unitFilter').val(null).trigger('change');
        $('#sifatFilter').val(null).trigger('change');
        $('#prioritasFilter').val(null).trigger('change');
        $('#tanggalMulai').val('');
        $('#tanggalSampai').val('');
        performSearch();
    });

    // Export functions
    $('#exportExcel').click(function() {
        exportResults('xlsx');
    });

    $('#exportCsv').click(function() {
        exportResults('csv');
    });

    $('#exportPdf').click(function() {
        exportResults('pdf');
    });

    // Perform search
    function performSearch() {
        var queryParams = {
            q: $('#searchQuery').val(),
            type: $('#typeFilter').val(),
            sort_by: $('#sortBy').val(),
            sort_order: $('#sortOrder').val(),
            status: $('#statusFilter').val(),
            klasifikasi_id: $('#klasifikasiFilter').val(),
            unit_id: $('#unitFilter').val(),
            sifat_id: $('#sifatFilter').val(),
            prioritas: $('#prioritasFilter').val(),
            tanggal_mulai: $('#tanggalMulai').val(),
            tanggal_sampai: $('#tanggalSampai').val()
        };

        $.ajax({
            url: '{{ route("search.api") }}',
            data: queryParams,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    table.clear();
                    table.rows.add(response.data);
                    table.draw();
                    
                    // Update URL without reload
                    var newUrl = window.location.pathname + '?' + $.param(queryParams);
                    history.pushState(null, '', newUrl);
                }
            },
            error: function(xhr) {
                console.error('Search error:', xhr);
                alert('Terjadi kesalahan saat mencari. Silakan coba lagi.');
            }
        });
    }

    // Export results
    function exportResults(format) {
        var queryParams = {
            q: $('#searchQuery').val(),
            type: $('#typeFilter').val(),
            sort_by: $('#sortBy').val(),
            sort_order: $('#sortOrder').val(),
            status: $('#statusFilter').val(),
            klasifikasi_id: $('#klasifikasiFilter').val(),
            unit_id: $('#unitFilter').val(),
            sifat_id: $('#sifatFilter').val(),
            prioritas: $('#prioritasFilter').val(),
            tanggal_mulai: $('#tanggalMulai').val(),
            tanggal_sampai: $('#tanggalSampai').val(),
            export_format: format
        };

        var queryString = $.param(queryParams);
        window.location.href = '{{ route("search.export") }}?' + queryString;
    }

    // Initial search if query exists
    @if(request('q'))
        performSearch();
    @endif
});
</script>
@endpush
