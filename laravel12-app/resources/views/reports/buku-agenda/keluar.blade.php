@extends('layouts.app-bootstrap')

@section('title', 'Buku Agenda Surat Keluar')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3><i class="bi bi-send"></i> Buku Agenda Surat Keluar</h3>
            <p class="text-muted">Laporan surat keluar periode {{ $periode }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('reports.export.buku-agenda', ['type' => 'keluar', 'periode' => $periode]) }}" 
               class="btn btn-danger" target="_blank">
                <i class="bi bi-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.buku-agenda.keluar') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Periode</label>
                    <input type="month" name="periode" class="form-control" value="{{ $periode }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Group By</label>
                    <select name="group_by" class="form-select">
                        <option value="status" {{ $groupBy == 'status' ? 'selected' : '' }}>Status</option>
                        <option value="unit" {{ $groupBy == 'unit' ? 'selected' : '' }}>Unit Pembuat</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('reports.buku-agenda.keluar') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Summary -->
    @if($stats->count() > 0)
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <strong>Statistik per {{ $groupBy === 'status' ? 'Status' : 'Unit' }}</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            @if($groupBy === 'status')
                            <th>Status</th>
                            @else
                            <th>Unit Pembuat</th>
                            @endif
                            <th>Total Surat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats as $stat)
                        <tr>
                            @if($groupBy === 'status')
                            <td>
                                <span class="badge bg-{{ $stat->status->color() ?? 'secondary' }}">
                                    {{ $stat->status ?? '-' }}
                                </span>
                            </td>
                            @else
                            <td>{{ $stat->nama_unit ?? '-' }}</td>
                            @endif
                            <td><strong>{{ $stat->total }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="12%">Nomor Surat</th>
                            <th width="12%">Tanggal Surat</th>
                            <th>Unit Pembuat</th>
                            <th>Tujuan</th>
                            <th>Perihal</th>
                            <th width="10%">Status</th>
                            <th width="10%">Sifat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suratKeluar as $index => $surat)
                        <tr>
                            <td>{{ $suratKeluar->firstItem() + $index }}</td>
                            <td><strong>{{ $surat->nomor_surat_final ?? '-' }}</strong></td>
                            <td>{{ $surat->tanggal_surat_final ? $surat->tanggal_surat_final->format('d/m/Y') : '-' }}</td>
                            <td>{{ $surat->unitPembuat->nama_unit ?? '-' }}</td>
                            <td>{{ $surat->tujuan ?? '-' }}</td>
                            <td>{{ Str::limit($surat->perihal, 50) }}</td>
                            <td>
                                <span class="badge bg-{{ $surat->status->color() ?? 'secondary' }}">
                                    {{ $surat->status ?? 'Draft' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $surat->sifat->nama_sifat ?? '-' }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Tidak ada data surat keluar untuk periode ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($suratKeluar->hasPages())
            <div class="mt-3">
                {{ $suratKeluar->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
