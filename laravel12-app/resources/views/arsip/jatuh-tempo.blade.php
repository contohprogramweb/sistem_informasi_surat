@extends('layouts.app')

@section('title', 'Jatuh Tempo Arsip')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Laporan Jatuh Tempo Arsip</h1>
                <div>
                    <a href="{{ route('arsip.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('arsip.jatuh-tempo-export', ['months_ahead' => $monthsAhead, 'type' => $type]) }}" class="btn btn-success">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('arsip.jatuh-tempo') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Bulan Ke Depan</label>
                    <select name="months_ahead" class="form-select">
                        @for($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" {{ $monthsAhead == $i ? 'selected' : '' }}>
                                {{ $i }} Bulan
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tipe</label>
                    <select name="type" class="form-select">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="aktif" {{ $type === 'aktif' ? 'selected' : '' }}>Jatuh Tempo Aktif</option>
                        <option value="inaktif" {{ $type === 'inaktif' ? 'selected' : '' }}>Jatuh Tempo Inaktif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="{{ route('arsip.jatuh-tempo') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Arsip Jatuh Tempo ({{ count($jatuhTempoList) }} arsip)</h5>
        </div>
        <div class="card-body">
            @if(count($jatuhTempoList) > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Nomor Surat</th>
                            <th>Perihal</th>
                            <th>Klasifikasi</th>
                            <th>Jatuh Tempo Type</th>
                            <th>Tanggal Jatuh Tempo</th>
                            <th>Sisa Bulan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jatuhTempoList as $item)
                        @php
                            $model = $item['model'];
                            $badgeClass = $item['sisa_bulan'] <= 1 ? 'bg-danger' : ($item['sisa_bulan'] <= 3 ? 'bg-warning text-dark' : 'bg-info text-dark');
                        @endphp
                        <tr>
                            <td>
                                @if($item['type'] === 'surat_masuk')
                                    <span class="badge bg-primary">Surat Masuk</span>
                                @else
                                    <span class="badge bg-success">Surat Keluar</span>
                                @endif
                            </td>
                            <td>{{ $model->nomor_surat ?? $model->nomor_surat_final }}</td>
                            <td>{{ Str::limit($model->perihal, 40) }}</td>
                            <td>{{ $model->klasifikasi?->nama ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $item['jatuh_tempo_type'] === 'aktif' ? 'bg-warning text-dark' : 'bg-danger' }}">
                                    {{ ucfirst($item['jatuh_tempo_type']) }}
                                </span>
                            </td>
                            <td>{{ $item['tanggal_jatuh_tempo']->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ $item['sisa_bulan'] }} bulan
                                </span>
                            </td>
                            <td>
                                @if($item['type'] === 'surat_masuk')
                                    <a href="{{ route('surat-masuk.show', $model->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <a href="{{ route('surat-keluar.show', $model->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center py-4">Tidak ada arsip yang jatuh tempo dalam periode ini.</p>
            @endif
        </div>
    </div>
</div>
@endsection
