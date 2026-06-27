@extends('layouts.app')

@section('title', 'Detail Surat Masuk')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-eye"></i> Detail Surat Masuk</h5>
                    <div>
                        @if($suratMasuk->status === 'Aktif')
                            <a href="{{ route('surat-masuk.edit', $suratMasuk->id) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i> Edit
                            </a>
                        @endif
                        <a href="{{ route('surat-masuk.index') }}" class="btn btn-sm btn-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Status Badges -->
                    <div class="mb-3">
                        <span class="badge {{ $suratMasuk->status_badge_class }}">{{ $suratMasuk->status }}</span>
                        <span class="badge {{ $suratMasuk->prioritas_badge_class }}">{{ $suratMasuk->prioritas }}</span>
                        @if($suratMasuk->agenda)
                            <span class="badge bg-info text-dark">No. Agenda: {{ $suratMasuk->agenda }}</span>
                        @endif
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th width="30%">Nomor Surat</th>
                                    <td>{{ $suratMasuk->nomor_surat }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Surat</th>
                                    <td>{{ $suratMasuk->tanggal_surat->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Tanggal Terima</th>
                                    <td>{{ $suratMasuk->tanggal_terima->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Cara Terima</th>
                                    <td>{{ $suratMasuk->cara_terima }}</td>
                                </tr>
                                <tr>
                                    <th>Penerima Fisik</th>
                                    <td>{{ $suratMasuk->penerima_fisik }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th width="30%">Pengirim</th>
                                    <td>{{ $suratMasuk->pengirim }}</td>
                                </tr>
                                <tr>
                                    <th>Klasifikasi</th>
                                    <td>{{ $suratMasuk->klasifikasi->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Sifat Surat</th>
                                    <td>{{ $suratMasuk->sifat->nama ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Prioritas</th>
                                    <td>{{ $suratMasuk->prioritas }}</td>
                                </tr>
                                <tr>
                                    <th>Unit Tujuan</th>
                                    <td>
                                        @foreach($suratMasuk->unitTujuan as $unit)
                                            <span class="badge bg-primary">{{ $unit->nama_unit }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Perihal:</label>
                            <p>{{ $suratMasuk->perihal }}</p>
                        </div>
                    </div>

                    @if($suratMasuk->ringkasan)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Ringkasan Isi:</label>
                            <p>{{ $suratMasuk->ringkasan }}</p>
                        </div>
                    </div>
                    @endif

                    @if($suratMasuk->indeks && count($suratMasuk->indeks) > 0)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Indeks:</label>
                            <div>
                                @foreach($suratMasuk->indeks as $index)
                                    <span class="badge bg-secondary">{{ $index }}</span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($suratMasuk->lampiran->count() > 0)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Lampiran:</label>
                            <ul class="list-group">
                                @foreach($suratMasuk->lampiran as $lampiran)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="bi bi-file-earmark"></i> 
                                            <a href="{{ Storage::url($lampiran->filename) }}" target="_blank">
                                                {{ $lampiran->original_name }}
                                            </a>
                                        </div>
                                        <div>
                                            <span class="badge bg-secondary me-2">{{ round($lampiran->size / 1024, 2) }} KB</span>
                                            <small class="text-muted">{{ $lampiran->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                    @endif

                    @if($suratMasuk->disposisi->count() > 0)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Disposisi:</label>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Dari</th>
                                            <th>Ke</th>
                                            <th>Instruksi</th>
                                            <th>Catatan</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($suratMasuk->disposisi as $disposisi)
                                            <tr>
                                                <td>{{ $disposisi->tanggal_disposisi->format('d/m/Y') }}</td>
                                                <td>{{ $disposisi->dariUser->name ?? '-' }}</td>
                                                <td>{{ $disposisi->kepadaUser->name ?? '-' }}</td>
                                                <td>{{ $disposisi->instruksi }}</td>
                                                <td>{{ $disposisi->catatan ?? '-' }}</td>
                                                <td>
                                                    <span class="badge {{ $disposisi->status === 'Selesai' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                        {{ $disposisi->status }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        @if($suratMasuk->status === 'Aktif' && $suratMasuk->canArchive())
                            <button type="button" class="btn btn-success btn-archive" data-id="{{ $suratMasuk->id }}">
                                <i class="bi bi-archive"></i> Arsipkan
                            </button>
                        @endif
                        @if($suratMasuk->status === 'Aktif')
                            <button type="button" class="btn btn-danger btn-delete" data-id="{{ $suratMasuk->id }}">
                                <i class="bi bi-trash"></i> Hapus
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Archive button handler
    $('.btn-archive').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Arsipkan surat ini?',
            text: "Surat akan dipindahkan ke arsip aktif",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, arsipkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('surat-masuk.archive', ':id') }}".replace(':id', id),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if(res.success) {
                            Swal.fire('Berhasil!', res.message, 'success').then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        let message = 'Terjadi kesalahan sistem';
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire('Gagal!', message, 'error');
                    }
                });
            }
        });
    });

    // Delete button handler
    $('.btn-delete').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Yakin hapus surat ini?',
            text: "Surat akan dihapus sementara dan dapat dipulihkan dalam 30 hari",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('surat-masuk.destroy', ':id') }}".replace(':id', id),
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(res) {
                        if(res.success) {
                            Swal.fire('Terhapus!', res.message, 'success').then(() => {
                                window.location.href = "{{ route('surat-masuk.index') }}";
                            });
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', 'Terjadi kesalahan sistem', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
