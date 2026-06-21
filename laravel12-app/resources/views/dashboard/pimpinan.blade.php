{{-- Dashboard Pimpinan - SIAP-SMK --}}
@extends('layouts.app-bootstrap')

@section('title', 'Dashboard Pimpinan')

@section('content')
<div class="container-fluid py-4">
    <!-- Header dengan Filter Cepat -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h2 class="mb-0">
                    <i class="bi bi-speedometer2 me-2"></i>Dashboard Pimpinan
                </h2>
                
                <!-- Filter Cepat -->
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('surat-masuk.index', ['prioritas' => 'Segera']) }}" 
                       class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-exclamation-triangle me-1"></i>Prioritas Tinggi/Segera
                    </a>
                    <a href="{{ route('surat-masuk.index', ['batas_hari_ini' => true]) }}" 
                       class="btn btn-outline-warning btn-sm">
                        <i class="bi bi-clock me-1"></i>Batas Waktu Hari Ini
                    </a>
                    <a href="{{ route('disposisi.saya', ['overdue' => true]) }}" 
                       class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-exclamation-octagon me-1"></i>Overdue
                        @if($countDisposisiOverdue > 0)
                            <span class="badge bg-danger rounded-pill">{{ $countDisposisiOverdue }}</span>
                        @endif
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 1: Statistik Cards -->
    <div class="row g-4 mb-4">
        <!-- Widget 1: Surat Masuk Perlu Disposisi -->
        <div class="col-md-4">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-inbox me-2"></i>Surat Perlu Disposisi</h6>
                    <span class="badge bg-light text-primary fs-6">{{ $countSuratPerluDisposisi }}</span>
                </div>
                <div class="card-body p-0">
                    @if($suratMasukPerluDisposisi->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($suratMasukPerluDisposisi as $surat)
                                <li class="list-group-item px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <a href="{{ route('surat-masuk.show', $surat->id) }}" 
                                               class="text-decoration-none fw-medium text-dark">
                                                {{ Str::limit($surat->perihal, 40) }}
                                            </a>
                                            <div class="small text-muted">
                                                <i class="bi bi-file-text me-1"></i>{{ $surat->nomor_surat }}
                                            </div>
                                            <div class="small">
                                                <span class="badge {{ $surat->prioritas_badge_class }}">{{ $surat->prioritas }}</span>
                                                <span class="text-muted ms-1">
                                                    <i class="bi bi-calendar me-1"></i>{{ $surat->tanggal_terima->format('d M Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('surat-masuk.index', ['perlu_disposisi' => true]) }}" 
                               class="btn btn-sm btn-outline-primary w-100">
                                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="bi bi-check-circle display-6"></i>
                            <p class="mt-2 mb-0">Tidak ada surat yang perlu disposisi</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Widget 2: Disposisi Berjalan dengan Overdue Indicator -->
        <div class="col-md-4">
            <div class="card h-100 border-warning shadow-sm">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-arrow-repeat me-2"></i>Disposisi Berjalan</h6>
                    @if($countDisposisiOverdue > 0)
                        <span class="badge bg-danger fs-6">{{ $countDisposisiOverdue }} Overdue</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if($disposisiBerjalan->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($disposisiBerjalan as $disposisi)
                                <li class="list-group-item px-3 py-2 {{ $disposisi->isOverdue() ? 'bg-danger-subtle' : '' }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <a href="{{ route('disposisi.show', $disposisi->id) }}" 
                                               class="text-decoration-none fw-medium text-dark">
                                                {{ Str::limit($disposisi->suratMasuk->perihal, 35) }}
                                            </a>
                                            <div class="small mt-1">
                                                <span class="badge bg-{{ $disposisi->status_color }}">{{ $disposisi->status }}</span>
                                                @if($disposisi->batas_waktu)
                                                    <span class="{{ $disposisi->isOverdue() ? 'text-danger fw-bold' : 'text-muted' }}">
                                                        <i class="bi bi-clock me-1"></i>
                                                        @if($disposisi->isOverdue())
                                                            Overdue ({{ $disposisi->batas_waktu->diffForHumans() }})
                                                        @else
                                                            {{ $disposisi->batas_waktu->format('d M Y') }}
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($disposisi->isOverdue())
                                            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('disposisi.saya') }}" 
                               class="btn btn-sm btn-outline-warning w-100">
                                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="bi bi-check-all display-6"></i>
                            <p class="mt-2 mb-0">Semua disposisi selesai</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Widget 3: Persetujuan Surat Keluar -->
        <div class="col-md-4">
            <div class="card h-100 border-info shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-signature me-2"></i>Persetujuan Surat Keluar</h6>
                    <span class="badge bg-light text-info fs-6">{{ $countPersetujuanMenunggu }}</span>
                </div>
                <div class="card-body p-0">
                    @if($persetujuanSuratKeluar->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($persetujuanSuratKeluar as $surat)
                                <li class="list-group-item px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <a href="{{ route('surat-keluar.show', $surat->id) }}" 
                                               class="text-decoration-none fw-medium text-dark">
                                                {{ Str::limit($surat->perihal, 40) }}
                                            </a>
                                            <div class="small text-muted">
                                                <i class="bi bi-building me-1"></i>{{ $surat->unitPembuat->nama ?? 'N/A' }}
                                            </div>
                                            <div class="small mt-1">
                                                <span class="badge bg-{{ $surat->status_badge_class }}">
                                                    {{ $surat->status->label() }}
                                                </span>
                                                <span class="text-muted ms-1">
                                                    <i class="bi bi-calendar me-1"></i>{{ $surat->updated_at->format('d M Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('surat-keluar.index', ['status' => 'review']) }}" 
                               class="btn btn-sm btn-outline-info w-100">
                                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="bi bi-check-circle display-6"></i>
                            <p class="mt-2 mb-0">Tidak ada persetujuan menunggu</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Real-time update polling setiap 60 detik
document.addEventListener('DOMContentLoaded', function() {
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
            // Update badge counts if needed
            console.log('Notification counts updated:', data);
        })
        .catch(error => console.error('Error fetching notification counts:', error));
    }, 60000); // 60 detik
});
</script>
@endpush
@endsection
