@extends('layouts.app-bootstrap')

@section('title', 'Laporan & Statistik')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-3"><i class="bi bi-graph-up"></i> Laporan & Statistik</h2>
            <p class="text-muted">Pusat laporan dan analisis data SIAP-SMK</p>
        </div>
    </div>

    <div class="row g-4">
        <!-- Buku Agenda -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-journal-bookmark-fill text-primary" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Buku Agenda</h5>
                    <p class="card-text text-muted small">Laporan surat masuk dan keluar dengan filter periode</p>
                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('reports.buku-agenda.masuk') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-inbox"></i> Surat Masuk
                        </a>
                        <a href="{{ route('reports.buku-agenda.keluar') }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-send"></i> Surat Keluar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rekap Disposisi -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-diagram-3-fill text-success" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Rekap Disposisi</h5>
                    <p class="card-text text-muted small">Ringkasan disposisi per unit dengan status penyelesaian</p>
                    <div class="mt-3">
                        <a href="{{ route('reports.rekap-disposisi') }}" class="btn btn-success btn-sm">
                            <i class="bi bi-table"></i> Lihat Rekap
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Arsip Jatuh Tempo -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-clock-history text-warning" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Arsip Jatuh Tempo</h5>
                    <p class="card-text text-muted small">Daftar arsip yang akan jatuh tempo dalam X bulan</p>
                    <div class="mt-3">
                        <a href="{{ route('reports.arsip-jatuh-tempo') }}" class="btn btn-warning btn-sm text-white">
                            <i class="bi bi-exclamation-triangle"></i> Cek Jatuh Tempo
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Audit Trail -->
        @if(auth()->user()->hasRole(['admin', 'admin_tu']))
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-shield-lock-fill text-danger" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Audit Trail</h5>
                    <p class="card-text text-muted small">Log aktivitas sistem dengan detail perubahan data</p>
                    <div class="mt-3">
                        <a href="{{ route('reports.audit-trail') }}" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-list-check"></i> Lihat Log
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Statistik Dashboard -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="bi bi-pie-chart-fill text-info" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="card-title">Statistik & Grafik</h5>
                    <p class="card-text text-muted small">Visualisasi data surat, disposisi, dan arsip</p>
                    <div class="mt-3">
                        <a href="{{ route('reports.statistik') }}" class="btn btn-info btn-sm text-white">
                            <i class="bi bi-bar-chart"></i> Buka Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
