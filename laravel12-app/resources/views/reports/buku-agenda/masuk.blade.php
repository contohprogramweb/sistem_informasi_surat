@extends('layouts.app-bootstrap')

@section('title', 'Buku Agenda Surat Masuk')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3><i class="bi bi-inbox"></i> Buku Agenda Surat Masuk</h3>
            <p class="text-muted">Laporan surat masuk periode {{ $periode }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('reports.export.buku-agenda', ['type' => 'masuk', 'periode' => $periode]) }}" 
               class="btn btn-danger" target="_blank">
                <i class="bi bi-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.buku-agenda.masuk') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Periode</label>
                    <input type="month" name="periode" class="form-control" value="{{ $periode }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Group By</label>
                    <select name="group_by" class="form-select">
                        <option value="bulan" {{ $groupBy == 'bulan' ? 'selected' : '' }}>Bulan</option>
                        <option value="klasifikasi" {{ $groupBy == 'klasifikasi' ? 'selected' : '' }}>Klasifikasi</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('reports.buku-agenda.masuk') }}" class="btn btn-outline-secondary">
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
            <strong>Statistik per {{ $groupBy === 'bulan' ? 'Bulan' : 'Klasifikasi' }}</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            @if($groupBy === 'bulan')
                            <th>Bulan</th>
                            <th>Total Surat</th>
                            @else
                            <th>Klasifikasi</th>
                            <th>Total Surat</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats as $stat)
                        <tr>
                            @if($groupBy === 'bulan')
                            <td>{{ \Carbon\Carbon::create()->month($stat->month)->format('F Y') }}</td>
                            @else
                            <td>{{ $stat->nama_klasifikasi ?? '-' }}</td>
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
                            <th width="10%">Agenda</th>
                            <th width="12%">Tanggal Terima</th>
                            <th width="15%">Nomor Surat</th>
                            <th>Pengirim</th>
                            <th>Perihal</th>
                            <th width="10%">Klasifikasi</th>
                            <th width="8%">Prioritas</th>
                            <th width="10%">Unit Tujuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suratMasuk as $index => $surat)
                        <tr>
                            <td>{{ $suratMasuk->firstItem() + $index }}</td>
                            <td><strong>{{ $surat->agenda ?? '-' }}</strong></td>
                            <td>{{ $surat->tanggal_terima->format('d/m/Y') }}</td>
                            <td>{{ $surat->nomor_surat ?? '-' }}</td>
                            <td>{{ $surat->pengirim ?? '-' }}</td>
                            <td>{{ Str::limit($surat->perihal, 50) }}</td>
                            <td>
                                <span class="badge bg-info">{{ $surat->klasifikasi->nama_klasifikasi ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $surat->prioritas_badge_class }}">
                                    {{ $surat->prioritas ?? 'Normal' }}
                                </span>
                            </td>
                            <td>
                                @foreach($surat->unitTujuan->take(2) as $unit)
                                <span class="badge bg-secondary">{{ $unit->nama_unit }}</span>
                                @endforeach
                                @if($surat->unitTujuan->count() > 2)
                                <span class="badge bg-secondary">+{{ $surat->unitTujuan->count() - 2 }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Tidak ada data surat masuk untuk periode ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($suratMasuk->hasPages())
            <div class="mt-3">
                {{ $suratMasuk->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
