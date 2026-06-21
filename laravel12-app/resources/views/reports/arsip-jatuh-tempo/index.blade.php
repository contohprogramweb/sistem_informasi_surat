@extends('layouts.app-bootstrap')

@section('title', 'Arsip Jatuh Tempo')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3><i class="bi bi-clock-history"></i> Arsip Jatuh Tempo</h3>
            <p class="text-muted">Daftar arsip yang akan jatuh tempo dalam {{ $bulanDepan }} bulan ke depan</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('reports.export.arsip-jatuh-tempo', ['retensi_status' => $retensiStatus, 'bulan_depan' => $bulanDepan]) }}" 
               class="btn btn-success">
                <i class="bi bi-file-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card bg-success-subtle border-success shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-success">Aktif (3 Bulan)</h5>
                    <h2 class="mb-0">{{ $stats['aktif_3_bulan'] }}</h2>
                    <small>Arsip aktif jatuh tempo</small>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-warning-subtle border-warning shadow-sm">
                <div class="card-body text-center">
                    <h5 class="card-title text-warning">Inaktif (3 Bulan)</h5>
                    <h2 class="mb-0">{{ $stats['inaktif_3_bulan'] }}</h2>
                    <small>Arsip inaktif jatuh tempo</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.arsip-jatuh-tempo') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Status Retensi</label>
                    <select name="retensi_status" class="form-select">
                        <option value="aktif" {{ $retensiStatus == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="inaktif" {{ $retensiStatus == 'inaktif' ? 'selected' : '' }}>Inaktif</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bulan Depan (X)</label>
                    <input type="number" name="bulan_depan" class="form-control" value="{{ $bulanDepan }}" min="1" max="24">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('reports.arsip-jatuh-tempo') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <strong>Daftar Arsip Jatuh Tempo</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Agenda</th>
                            <th width="12%">Nomor Surat</th>
                            <th>Tanggal Surat</th>
                            <th>Pengirim</th>
                            <th>Perihal</th>
                            <th>Klasifikasi</th>
                            <th width="10%">Status</th>
                            <th width="12%">Jatuh Tempo</th>
                            <th width="8%">Sisa Hari</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($arsipJatuhTempo as $index => $arsip)
                        @php
                            $jatuhTempo = $retensiStatus === 'aktif' ? $arsip->tanggal_jatuh_aktif : $arsip->tanggal_jatuh_inaktif;
                            $sisaHari = $jatuhTempo ? now()->diffInDays($jatuhTempo, false) : null;
                            $isUrgent = $sisaHari !== null && $sisaHari <= 30;
                        @endphp
                        <tr class="{{ $isUrgent ? 'table-warning' : '' }}">
                            <td>{{ $arsipJatuhTempo->firstItem() + $index }}</td>
                            <td><strong>{{ $arsip->agenda ?? '-' }}</strong></td>
                            <td>{{ $arsip->nomor_surat ?? '-' }}</td>
                            <td>{{ $arsip->tanggal_surat ? $arsip->tanggal_surat->format('d/m/Y') : '-' }}</td>
                            <td>{{ $arsip->pengirim ?? '-' }}</td>
                            <td>{{ Str::limit($arsip->perihal, 40) }}</td>
                            <td>{{ $arsip->klasifikasi->nama_klasifikasi ?? '-' }}</td>
                            <td>
                                <span class="badge bg-{{ $arsip->arsip_status_badge_class }}">
                                    {{ ucfirst($arsip->status_arsip ?? '-') }}
                                </span>
                            </td>
                            <td>
                                {{ $jatuhTempo ? $jatuhTempo->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                @if($sisaHari !== null)
                                <strong class="{{ $sisaHari <= 30 ? 'text-danger' : 'text-success' }}">
                                    {{ $sisaHari }} hari
                                </strong>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Tidak ada arsip yang jatuh tempo untuk kriteria ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($arsipJatuhTempo->hasPages())
            <div class="mt-3">
                {{ $arsipJatuhTempo->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
