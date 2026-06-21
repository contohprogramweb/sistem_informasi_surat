{{-- Dashboard Staff TU - SIAP-SMK --}}
@extends('layouts.app-bootstrap')

@section('title', 'Dashboard Staff TU')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i class="bi bi-person-workspace me-2"></i>Dashboard Staff Tata Usaha
            </h2>
            <p class="text-muted">Kelola surat masuk, surat keluar, dan arsip</p>
        </div>
    </div>

    <!-- Row 1: Statistik Cards -->
    <div class="row g-4 mb-4">
        <!-- Surat Masuk Hari Ini -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-primary shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Surat Masuk Hari Ini</h6>
                            <h3 class="mb-0 text-primary fw-bold">{{ $suratMasukHariIni }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-inbox-fill text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Surat Keluar Hari Ini -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-success shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Surat Keluar Hari Ini</h6>
                            <h3 class="mb-0 text-success fw-bold">{{ $suratKeluarHariIni }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-envelope-fill text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Arsip Jatuh Tempo -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-warning shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Arsip Jatuh Tempo</h6>
                            <h3 class="mb-0 text-warning fw-bold">{{ $arsipJatuhTempo }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-calendar-x-fill text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Disposisi Terbuka -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-info shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">Disposisi Terbuka</h6>
                            <h3 class="mb-0 text-info fw-bold">{{ $disposisiTerbuka }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-arrow-repeat text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2: Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="bi bi-lightning-charge me-2"></i>Aksi Cepat</h5>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('surat-masuk.create') }}" class="btn btn-primary w-100 py-3 shadow-sm">
                <i class="bi bi-plus-circle d-block fs-3 mb-2"></i>
                <span class="fw-medium">Input Surat Masuk</span>
            </a>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('surat-keluar.create') }}" class="btn btn-success w-100 py-3 shadow-sm">
                <i class="bi bi-file-earmark-plus d-block fs-3 mb-2"></i>
                <span class="fw-medium">Buat Surat Keluar</span>
            </a>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('arsip.index') }}" class="btn btn-warning w-100 py-3 shadow-sm">
                <i class="bi bi-folder d-block fs-3 mb-2"></i>
                <span class="fw-medium">Kelola Arsip</span>
            </a>
        </div>
        
        <div class="col-12 col-sm-6 col-lg-3">
            <a href="{{ route('disposisi.saya') }}" class="btn btn-info w-100 py-3 shadow-sm">
                <i class="bi bi-share d-block fs-3 mb-2"></i>
                <span class="fw-medium">Lihat Disposisi</span>
            </a>
        </div>
    </div>

    <!-- Row 3: Chart -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h5 class="mb-3"><i class="bi bi-graph-up me-2"></i>Statistik Surat (6 Bulan Terakhir)</h5>
        </div>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <canvas id="suratChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart data dari controller
    const chartData = @json($chartData);
    
    const ctx = document.getElementById('suratChart').getContext('2d');
    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [
                {
                    label: 'Surat Masuk',
                    data: chartData.masuk,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Surat Keluar',
                    data: chartData.keluar,
                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.raw + ' surat';
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    
    // Real-time update polling setiap 60 detik
    setInterval(function() {
        fetch('{{ route("api.dashboard.notification-counts") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Notification counts updated:', data);
        })
        .catch(error => console.error('Error fetching notification counts:', error));
    }, 60000); // 60 detik
});
</script>
@endpush
@endsection
