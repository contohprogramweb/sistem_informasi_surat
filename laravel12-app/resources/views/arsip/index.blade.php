@extends('layouts.app')

@section('title', 'Arsip & Retensi')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Arsip & Retensi</h1>
                <div>
                    <a href="{{ route('arsip.jatuh-tempo') }}" class="btn btn-warning">
                        <i class="fas fa-clock"></i> Jatuh Tempo
                    </a>
                    <a href="{{ route('arsip.berita-acara.index') }}" class="btn btn-info">
                        <i class="fas fa-file-alt"></i> Berita Acara
                    </a>
                    <a href="{{ route('arsip.trash') }}" class="btn btn-secondary">
                        <i class="fas fa-trash"></i> Trash
                    </a>
                    <a href="{{ route('arsip.notifications') }}" class="btn btn-primary">
                        <i class="fas fa-bell"></i> Notifikasi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Arsip Aktif</h5>
                    <h2>{{ $stats['total_aktif'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h5 class="card-title">Arsip Inaktif</h5>
                    <h2>{{ $stats['total_inaktif'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Dimusnahkan</h5>
                    <h2>{{ $stats['total_dimusnahkan'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-dark">
                <div class="card-body">
                    <h5 class="card-title">Jatuh Tempo Bulan Ini</h5>
                    <h2>{{ $stats['jatuh_tempo_bulan_ini'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('arsip.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status Arsip</label>
                    <select name="status_arsip" class="form-select">
                        <option value="">Semua</option>
                        <option value="aktif" {{ $filter['status_arsip'] ?? '' === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="inaktif" {{ $filter['status_arsip'] ?? '' === 'inaktif' ? 'selected' : '' }}>Inaktif</option>
                        <option value="dimusnahkan" {{ $filter['status_arsip'] ?? '' === 'dimusnahkan' ? 'selected' : '' }}>Dimusnahkan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Klasifikasi</label>
                    <select name="klasifikasi_id" class="form-select">
                        <option value="">Semua</option>
                        @foreach(\App\Models\KlasifikasiArsip::all() as $ka)
                            <option value="{{ $ka->id }}" {{ ($filter['klasifikasi_id'] ?? '') == $ka->id ? 'selected' : '' }}>
                                {{ $ka->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('arsip.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Surat Masuk Archived -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Surat Masuk Terarsip</h5>
        </div>
        <div class="card-body">
            @if($suratMasuk->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nomor Surat</th>
                            <th>Tanggal Terima</th>
                            <th>Pengirim</th>
                            <th>Perihal</th>
                            <th>Klasifikasi</th>
                            <th>Status Arsip</th>
                            <th>Jatuh Tempo Aktif</th>
                            <th>Jatuh Tempo Inaktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suratMasuk as $surat)
                        <tr>
                            <td>{{ $surat->nomor_surat }}</td>
                            <td>{{ $surat->tanggal_terima->format('d/m/Y') }}</td>
                            <td>{{ $surat->pengirim }}</td>
                            <td>{{ Str::limit($surat->perihal, 30) }}</td>
                            <td>{{ $surat->klasifikasi?->nama ?? '-' }}</td>
                            <td><span class="badge {{ $surat->arsip_status_badge_class }}">{{ ucfirst($surat->status_arsip) }}</span></td>
                            <td>{{ $surat->tanggal_jatuh_aktif?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $surat->tanggal_jatuh_inaktif?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <a href="{{ route('surat-masuk.show', $surat->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $suratMasuk->links() }}
            @else
            <p class="text-muted">Tidak ada surat masuk terarsip.</p>
            @endif
        </div>
    </div>

    <!-- Surat Keluar Archived -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Surat Keluar Terarsip</h5>
        </div>
        <div class="card-body">
            @if($suratKeluar->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nomor Surat</th>
                            <th>Tanggal Surat</th>
                            <th>Tujuan</th>
                            <th>Perihal</th>
                            <th>Klasifikasi</th>
                            <th>Status Arsip</th>
                            <th>Jatuh Tempo Aktif</th>
                            <th>Jatuh Tempo Inaktif</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suratKeluar as $surat)
                        <tr>
                            <td>{{ $surat->nomor_surat_final ?? '-' }}</td>
                            <td>{{ $surat->tanggal_surat_final?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $surat->tujuan }}</td>
                            <td>{{ Str::limit($surat->perihal, 30) }}</td>
                            <td>{{ $surat->klasifikasi?->nama ?? '-' }}</td>
                            <td><span class="badge {{ $surat->arsip_status_badge_class }}">{{ ucfirst($surat->status_arsip) }}</span></td>
                            <td>{{ $surat->tanggal_jatuh_aktif?->format('d/m/Y') ?? '-' }}</td>
                            <td>{{ $surat->tanggal_jatuh_inaktif?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <a href="{{ route('surat-keluar.show', $surat->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $suratKeluar->links() }}
            @else
            <p class="text-muted">Tidak ada surat keluar terarsip.</p>
            @endif
        </div>
    </div>
</div>
@endsection
