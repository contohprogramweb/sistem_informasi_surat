@extends('layouts.app')

@section('title', 'Surat Keluar')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="bi bi-send"></i> Surat Keluar</h5>
                        @can('surat_keluar.create')
                        <a href="{{ route('surat-keluar.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Buat Surat Baru
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3 g-3">
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" id="filterStatus">
                                <option value="">Semua Status</option>
                                <option value="draft">Draft</option>
                                <option value="review">Review</option>
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                                <option value="siap_ttd">Siap TTD</option>
                                <option value="tertandatangani">Tertandatangani</option>
                                <option value="terkirim">Terkirim</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" id="filterKlasifikasi">
                                <option value="">Semua Klasifikasi</option>
                                @foreach($klasifikasis as $klas)
                                    <option value="{{ $klas->id }}">{{ $klas->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select form-select-sm" id="filterSifat">
                                <option value="">Semua Sifat</option>
                                @foreach($sifats as $sifat)
                                    <option value="{{ $sifat->id }}">{{ $sifat->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control form-control-sm" id="filterFromDate" placeholder="Dari Tanggal">
                        </div>
                        <div class="col-md-2">
                            <input type="date" class="form-control form-control-sm" id="filterToDate" placeholder="Sampai Tanggal">
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table id="suratKeluarTable" class="table table-hover" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Tanggal Dibuat</th>
                                    <th>Tujuan</th>
                                    <th>Perihal</th>
                                    <th>Klasifikasi</th>
                                    <th>Sifat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>
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
    var table = $('#suratKeluarTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('surat-keluar.index') }}",
            data: function(d) {
                d.status = $('#filterStatus').val();
                d.klasifikasi_id = $('#filterKlasifikasi').val();
                d.sifat_id = $('#filterSifat').val();
                d.from_date = $('#filterFromDate').val();
                d.to_date = $('#filterToDate').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'created_at', name: 'created_at'},
            {data: 'tujuan', name: 'tujuan'},
            {data: 'perihal', name: 'perihal'},
            {data: 'klasifikasi.nama', name: 'klasifikasi.nama'},
            {data: 'sifat.nama', name: 'sifat.nama'},
            {data: 'status_badge', name: 'status'},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[1, 'desc']]
    });

    // Filter change events
    $('#filterStatus, #filterKlasifikasi, #filterSifat, #filterFromDate, #filterToDate').on('change keyup', function() {
        table.draw();
    });

    // Delete action
    $(document).on('click', '.btn-delete', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Yakin hapus?',
            text: "Surat keluar akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('surat-keluar.destroy', ':id') }}".replace(':id', id),
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        if(res.success) {
                            table.draw();
                            Swal.fire('Terhapus!', res.message, 'success');
                        } else {
                            Swal.fire('Gagal!', res.message, 'error');
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
