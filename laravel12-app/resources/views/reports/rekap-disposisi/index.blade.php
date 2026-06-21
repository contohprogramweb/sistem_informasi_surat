@extends('layouts.app-bootstrap')

@section('title', 'Rekap Disposisi')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3><i class="bi bi-diagram-3"></i> Rekap Disposisi</h3>
            <p class="text-muted">Ringkasan disposisi {{ $tanggalMulai }} s/d {{ $tanggalSampai }}</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('reports.export.rekap-disposisi', ['unit_id' => $unitId, 'tanggal_mulai' => $tanggalMulai, 'tanggal_sampai' => $tanggalSampai]) }}" 
               class="btn btn-success">
                <i class="bi bi-file-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.rekap-disposisi') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Unit</label>
                    <select name="unit_id" class="form-select">
                        <option value="">Semua Unit</option>
                        @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ $unitId == $unit->id ? 'selected' : '' }}>
                            {{ $unit->nama_unit }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="{{ $tanggalMulai }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" class="form-control" value="{{ $tanggalSampai }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary per Unit -->
    @if($summary->count() > 0)
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-light">
            <strong>Ringkasan per Unit</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Unit</th>
                            <th class="text-center">Total</th>
                            <th class="text-center bg-success text-white">Selesai</th>
                            <th class="text-center bg-warning">Belum Selesai</th>
                            <th class="text-center bg-danger text-white">Overdue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($summary as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td><strong>{{ $item->nama_unit ?? 'Tanpa Unit' }}</strong></td>
                            <td class="text-center"><strong>{{ $item->total }}</strong></td>
                            <td class="text-center bg-success-subtle">{{ $item->selesai }}</td>
                            <td class="text-center bg-warning-subtle">{{ $item->belum_selesai }}</td>
                            <td class="text-center bg-danger-subtle text-danger"><strong>{{ $item->overdue }}</strong></td>
                        </tr>
                        @endforeach
                        <tr class="table-secondary">
                            <td colspan="2" class="text-end"><strong>Total</strong></td>
                            <td class="text-center"><strong>{{ $summary->sum('total') }}</strong></td>
                            <td class="text-center"><strong>{{ $summary->sum('selesai') }}</strong></td>
                            <td class="text-center"><strong>{{ $summary->sum('belum_selesai') }}</strong></td>
                            <td class="text-center"><strong>{{ $summary->sum('overdue') }}</strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Detail Disposisi -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <strong>Detail Disposisi</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Agenda</th>
                            <th width="12%">Tanggal</th>
                            <th>Dari</th>
                            <th>Kepada</th>
                            <th>Perihal</th>
                            <th>Instruksi</th>
                            <th width="10%">Status</th>
                            <th width="12%">Batas Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($disposisi as $index => $disp)
                        <tr class="{{ $disp->isOverdue() ? 'table-danger' : '' }}">
                            <td>{{ $disposisi->firstItem() + $index }}</td>
                            <td><strong>{{ $disp->suratMasuk->agenda ?? '-' }}</strong></td>
                            <td>{{ $disp->created_at->format('d/m/Y') }}</td>
                            <td>{{ $disp->dariUser->name ?? '-' }}</td>
                            <td>{{ $disp->keUser->name ?? '-' }}</td>
                            <td>{{ Str::limit($disp->suratMasuk->perihal, 40) }}</td>
                            <td>
                                @if($disp->instruksi)
                                @foreach($disp->instruksi as $instr)
                                <span class="badge bg-info">{{ $instr }}</span>
                                @endforeach
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $disp->status_color ?? 'secondary' }}">
                                    {{ $disp->status }}
                                </span>
                            </td>
                            <td>
                                @if($disp->batas_waktu)
                                {{ $disp->batas_waktu->format('d/m/Y') }}
                                @if($disp->isOverdue())
                                <br><small class="text-danger fw-bold">OVERDUE</small>
                                @endif
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Tidak ada data disposisi untuk periode ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($disposisi->hasPages())
            <div class="mt-3">
                {{ $disposisi->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
