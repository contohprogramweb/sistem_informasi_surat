{{-- Dashboard Kabag - SIAP-SMK --}}
@extends('layouts.app-bootstrap')

@section('title', 'Dashboard Kabag')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0">
                <i class="bi bi-briefcase me-2"></i>Dashboard Kepala Bagian
            </h2>
            @if($userUnit)
                <p class="text-muted">{{ $userUnit->nama ?? '' }}</p>
            @endif
        </div>
    </div>

    <!-- Row 1: Statistik Cards -->
    <div class="row g-4 mb-4">
        <!-- Widget 1: Disposisi Masuk (Belum Selesai) -->
        <div class="col-md-4">
            <div class="card h-100 border-primary shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-inbox me-2"></i>Disposisi Masuk</h6>
                    <span class="badge bg-light text-primary fs-6">{{ $countDisposisiMasuk }}</span>
                </div>
                <div class="card-body p-0">
                    @if($disposisiMasuk->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($disposisiMasuk as $disposisi)
                                <li class="list-group-item px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <a href="{{ route('disposisi.show', $disposisi->id) }}" 
                                               class="text-decoration-none fw-medium text-dark">
                                                {{ Str::limit($disposisi->suratMasuk->perihal, 40) }}
                                            </a>
                                            <div class="small text-muted">
                                                <i class="bi bi-person me-1"></i>Dari: {{ $disposisi->dariUser->name ?? 'N/A' }}
                                            </div>
                                            <div class="small mt-1">
                                                <span class="badge bg-{{ $disposisi->status_color }}">{{ $disposisi->status }}</span>
                                                @if($disposisi->batas_waktu)
                                                    <span class="{{ $disposisi->isOverdue() ? 'text-danger fw-bold' : 'text-muted' }}">
                                                        <i class="bi bi-clock me-1"></i>{{ $disposisi->batas_waktu->format('d M Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('disposisi.saya') }}" 
                               class="btn btn-sm btn-outline-primary w-100">
                                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="bi bi-check-circle display-6"></i>
                            <p class="mt-2 mb-0">Tidak ada disposisi masuk</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Widget 2: Disposisi yang Saya Teruskan -->
        <div class="col-md-4">
            <div class="card h-100 border-success shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-share me-2"></i>Disposisi Diteruskan</h6>
                </div>
                <div class="card-body p-0">
                    @if($disposisiDiteruskan->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($disposisiDiteruskan as $disposisi)
                                <li class="list-group-item px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <a href="{{ route('disposisi.show', $disposisi->id) }}" 
                                               class="text-decoration-none fw-medium text-dark">
                                                {{ Str::limit($disposisi->suratMasuk->perihal, 35) }}
                                            </a>
                                            <div class="small text-muted">
                                                <i class="bi bi-person me-1"></i>Ke: {{ $disposisi->keUser->name ?? 'N/A' }}
                                            </div>
                                            <div class="small mt-1">
                                                <span class="badge bg-{{ $disposisi->status_color }}">{{ $disposisi->status }}</span>
                                                <span class="text-muted ms-1">
                                                    <i class="bi bi-calendar me-1"></i>{{ $disposisi->updated_at->format('d M Y') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('disposisi.saya', ['diteruskan' => true]) }}" 
                               class="btn btn-sm btn-outline-success w-100">
                                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="bi bi-share display-6"></i>
                            <p class="mt-2 mb-0">Belum ada disposisi diteruskan</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Widget 3: Surat Keluar Unit -->
        <div class="col-md-4">
            <div class="card h-100 border-warning shadow-sm">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="bi bi-envelope me-2"></i>Surat Keluar Unit</h6>
                    <div>
                        <span class="badge bg-secondary me-1">{{ $draftCount }} Draft</span>
                        <span class="badge bg-info text-dark">{{ $reviewCount }} Review</span>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($suratKeluarUnit && $suratKeluarUnit->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($suratKeluarUnit as $surat)
                                <li class="list-group-item px-3 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <a href="{{ route('surat-keluar.show', $surat->id) }}" 
                                               class="text-decoration-none fw-medium text-dark">
                                                {{ Str::limit($surat->perihal, 40) }}
                                            </a>
                                            <div class="small text-muted">
                                                <i class="bi bi-file-text me-1"></i>{{ $surat->nomor_surat_final ?? 'Draft' }}
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
                            <a href="{{ route('surat-keluar.index') }}" 
                               class="btn btn-sm btn-outline-warning w-100">
                                Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @else
                        <div class="p-3 text-center text-muted">
                            <i class="bi bi-envelope display-6"></i>
                            <p class="mt-2 mb-0">Belum ada surat keluar</p>
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
            console.log('Notification counts updated:', data);
        })
        .catch(error => console.error('Error fetching notification counts:', error));
    }, 60000); // 60 detik
});
</script>
@endpush
@endsection
