@extends('layouts.app-bootstrap')

@section('title', 'Statistik & Grafik')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3><i class="bi bi-pie-chart"></i> Statistik & Grafik</h3>
            <p class="text-muted">Visualisasi data SIAP-SMK tahun {{ $tahun }}</p>
        </div>
        <div class="col-md-4 text-end">
            <select id="tahunSelect" class="form-select d-inline-block w-auto" onchange="location.href='?tahun='+this.value">
                @for($i = now()->year - 2; $i <= now()->year + 1; $i++)
                <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4 col-lg-2">
            <div class="card bg-primary-subtle border-primary shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-primary">Surat Masuk</h6>
                    <h3 class="mb-0">{{ number_format($summary['surat_masuk_total']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card bg-success-subtle border-success shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-success">Surat Keluar</h6>
                    <h3 class="mb-0">{{ number_format($summary['surat_keluar_total']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card bg-info-subtle border-info shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-info">Total Disposisi</h6>
                    <h3 class="mb-0">{{ number_format($summary['disposisi_total']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card bg-warning-subtle border-warning shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-warning">Disposisi Selesai</h6>
                    <h3 class="mb-0">{{ number_format($summary['disposisi_selesai']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card bg-secondary-subtle border-secondary shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-secondary">Arsip Aktif</h6>
                    <h3 class="mb-0">{{ number_format($summary['arsip_aktif']) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 col-lg-2">
            <div class="card bg-danger-subtle border-danger shadow-sm">
                <div class="card-body text-center">
                    <h6 class="text-danger">Jatuh Tempo</h6>
                    <h3 class="mb-0">{{ number_format($summary['arsip_jatuh_tempo']) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row g-4 mb-4">
        <!-- Surat Masuk/Keluar per Bulan -->
        <div class="col-lg-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <strong><i class="bi bi-bar-chart-line"></i> Surat Masuk & Keluar per Bulan ({{ $tahun }})</strong>
                </div>
                <div class="card-body">
                    <canvas id="chartSuratPerBulan" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Disposisi per Status -->
        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light">
                    <strong><i class="bi bi-pie-chart"></i> Disposisi per Status</strong>
                </div>
                <div class="card-body">
                    <canvas id="chartDisposisiStatus" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="row g-4">
        <!-- Arsip per Klasifikasi -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <strong><i class="bi bi-bar-chart"></i> Arsip per Klasifikasi (Top 10)</strong>
                </div>
                <div class="card-body">
                    <canvas id="chartArsipKlasifikasi" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart Surat Masuk/Keluar per Bulan
    const ctxSurat = document.getElementById('chartSuratPerBulan').getContext('2d');
    new Chart(ctxSurat, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [
                {
                    label: 'Surat Masuk',
                    data: {!! json_encode(array_fill(0, 12, 0)) !!},
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Surat Keluar',
                    data: {!! json_encode(array_fill(0, 12, 0)) !!},
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Fill data from server
    @foreach($suratMasukPerBulan as $item)
    chartSurat.data.datasets[0].data[{{ $item->month - 1 }}] = {{ $item->total }};
    @endforeach
    @foreach($suratKeluarPerBulan as $item)
    chartSurat.data.datasets[1].data[{{ $item->month - 1 }}] = {{ $item->total }};
    @endforeach
    chartSurat.update();

    // Chart Disposisi per Status
    const ctxDisposisi = document.getElementById('chartDisposisiStatus').getContext('2d');
    new Chart(ctxDisposisi, {
        type: 'pie',
        data: {
            labels: {!! json_encode($disposisiPerStatus->pluck('status')->toArray()) !!},
            datasets: [{
                data: {!! json_encode($disposisiPerStatus->pluck('total')->toArray()) !!},
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Chart Arsip per Klasifikasi
    const ctxKlasifikasi = document.getElementById('chartArsipKlasifikasi').getContext('2d');
    new Chart(ctxKlasifikasi, {
        type: 'bar',
        data: {
            labels: {!! json_encode($arsipPerKlasifikasi->pluck('nama_klasifikasi')->toArray()) !!},
            datasets: [{
                label: 'Jumlah Arsip',
                data: {!! json_encode($arsipPerKlasifikasi->pluck('total')->toArray()) !!},
                backgroundColor: 'rgba(153, 102, 255, 0.7)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
});
</script>
@endpush
@endsection
