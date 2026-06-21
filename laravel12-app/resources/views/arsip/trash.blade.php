@extends('layouts.app')

@section('title', 'Trash - Arsip Dihapus')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Trash - Arsip Dihapus Sementara</h1>
                <a href="{{ route('arsip.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Type -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('arsip.trash') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Tipe Arsip</label>
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Semua</option>
                        <option value="surat_masuk" {{ $type === 'surat_masuk' ? 'selected' : '' }}>Surat Masuk</option>
                        <option value="surat_keluar" {{ $type === 'surat_keluar' ? 'selected' : '' }}>Surat Keluar</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Info Box -->
    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i>
        <strong>Informasi:</strong> Arsip yang dihapus akan tetap berada di trash selama 30 hari sebelum dihapus permanen.
        Selama periode ini, arsip dapat dipulihkan. Setelah 30 hari, arsip akan dihapus permanen termasuk lampiran fisiknya.
    </div>

    <!-- Trash Items -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Arsip Dihapus ({{ count($trashItems) }} item)</h5>
        </div>
        <div class="card-body">
            @if(count($trashItems) > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Nomor Surat</th>
                            <th>Perihal</th>
                            <th>Alasan Hapus</th>
                            <th>Dihapus Sampai</th>
                            <th>Sisa Hari</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($trashItems as $item)
                        @php
                            $model = $item['model'];
                            $sisaHari = max(0, now()->diffInDays($model->deleted_until, false));
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
                            <td>{{ $model->alasan_hapus ?? '-' }}</td>
                            <td>{{ $model->deleted_until->format('d/m/Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $sisaHari <= 7 ? 'bg-danger' : ($sisaHari <= 15 ? 'bg-warning text-dark' : 'bg-success') }}">
                                    {{ $sisaHari }} hari
                                </span>
                            </td>
                            <td>
                                @if($item['can_restore'])
                                    <form action="{{ route('arsip.restore', ['type' => $item['type'], 'id' => $model->id]) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success" 
                                                onclick="return confirm('Pulihkan arsip ini?')">
                                            <i class="fas fa-undo"></i> Pulihkan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-muted small">Tidak dapat dipulihkan</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center py-4">Tidak ada arsip di trash.</p>
            @endif
        </div>
    </div>
</div>
@endsection
