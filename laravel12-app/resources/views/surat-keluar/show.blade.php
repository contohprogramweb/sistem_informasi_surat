@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Detail Surat Keluar</h1>
        <div>
            <a href="{{ route('surat-keluar.edit', $suratKeluar->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('surat-keluar.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Informasi Surat</h6>
                    <span class="badge bg-success">{{ $suratKeluar->status }}</span>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th width="30%">Nomor Surat</th>
                            <td>{{ $suratKeluar->nomor_surat }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Surat</th>
                            <td>{{ $suratKeluar->tanggal_surat->format('d F Y') }}</td>
                        </tr>
                        <tr>
                            <th>Tujuan</th>
                            <td>{{ $suratKeluar->tujuan }}</td>
                        </tr>
                        <tr>
                            <th>Perihal</th>
                            <td>{{ $suratKeluar->perihal }}</td>
                        </tr>
                        <tr>
                            <th>Unit Pengirim</th>
                            <td>{{ $suratKeluar->unit->nama_unit ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Klasifikasi Arsip</th>
                            <td>{{ $suratKeluar->klasifikasiArsip->uraian ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Sifat Surat</th>
                            <td>{{ $suratKeluar->sifatSurat->nama_sifat ?? 'Biasa' }}</td>
                        </tr>
                        <tr>
                            <th>Dibuat Oleh</th>
                            <td>{{ $suratKeluar->createdBy->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Tanggal Dibuat</th>
                            <td>{{ $suratKeluar->created_at->format('d F Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($suratKeluar->file_scan)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">File Surat</h6>
                </div>
                <div class="card-body text-center">
                    <iframe src="{{ Storage::url($suratKeluar->file_scan) }}" width="100%" height="500px" style="border: 1px solid #ddd;"></iframe>
                    <div class="mt-3">
                        <a href="{{ Storage::url($suratKeluar->file_scan) }}" download class="btn btn-primary">
                            <i class="fas fa-download"></i> Download PDF
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Status TTE (Tanda Tangan Elektronik)</h6>
                </div>
                <div class="card-body">
                    @if($suratKeluar->signedAt)
                        <div class="alert alert-success">
                            <strong><i class="fas fa-check-circle"></i> Sudah Ditandatangani</strong>
                            <hr class="my-2">
                            <small>
                                Tanggal: {{ $suratKeluar->signedAt->format('d F Y H:i') }}<br>
                                Oleh: {{ $suratKeluar->signedBy->name ?? '-' }}
                            </small>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <strong><i class="fas fa-clock"></i> Belum Ditandatangani</strong>
                            <hr class="my-2">
                            <small>Surat ini belum ditandatangani secara elektronik.</small>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Riwayat Disposisi</h6>
                </div>
                <div class="card-body">
                    @if($suratKeluar->disposisis->count() > 0)
                        <div class="timeline">
                            @foreach($suratKeluar->disposisis as $disposisi)
                                <div class="mb-3 pb-3 border-bottom">
                                    <small class="text-muted">{{ $disposisi->created_at->diffForHumans() }}</small>
                                    <p class="mb-1"><strong>{{ $disposisi->unitTujuan->nama_unit ?? '-' }}</strong></p>
                                    <p class="mb-1 small">{{ Str::limit($disposisi->instruksi, 50) }}</p>
                                    <span class="badge bg-{{ $disposisi->sifat == 'Segera' ? 'danger' : ($disposisi->sifat == 'Penting' ? 'warning' : 'info') }}">
                                        {{ $disposisi->sifat }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center mb-0">Belum ada disposisi.</p>
                    @endif
                </div>
            </div>

            @can('delete', $suratKeluar)
            <div class="card shadow mb-4 border-danger">
                <div class="card-header bg-danger text-white py-3">
                    <h6 class="m-0 font-weight-bold">Zona Bahaya</h6>
                </div>
                <div class="card-body">
                    <p class="mb-3 small">Menghapus surat ini akan menghapus semua data terkait termasuk disposisi dan file lampiran.</p>
                    <form action="{{ route('surat-keluar.destroy', $suratKeluar->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus surat ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fas fa-trash"></i> Hapus Surat
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 20px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
</style>
@endpush
