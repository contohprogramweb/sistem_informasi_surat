@extends('layouts.app')

@section('title', 'Surat Masuk')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-inbox"></i> Daftar Surat Masuk</h5>
                    <a href="{{ route('surat-masuk.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Input Surat Masuk
                    </a>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-3 g-2">
                        <div class="col-md-2">
                            <input type="text" id="filter-tanggal-mulai" class="form-control form-control-sm" placeholder="Tanggal Mulai">
                        </div>
                        <div class="col-md-2">
                            <input type="text" id="filter-tanggal-sampai" class="form-control form-control-sm" placeholder="Tanggal Sampai">
                        </div>
                        <div class="col-md-2">
                            <select id="filter-klasifikasi" class="form-select form-select-sm">
                                <option value="">Semua Klasifikasi</option>
                                @foreach($klasifikasis as $klas)
                                    <option value="{{ $klas->id }}">{{ $klas->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="filter-unit" class="form-select form-select-sm">
                                <option value="">Semua Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="filter-sifat" class="form-select form-select-sm">
                                <option value="">Semua Sifat</option>
                                @foreach($sifatSurats as $sifat)
                                    <option value="{{ $sifat->id }}">{{ $sifat->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select id="filter-prioritas" class="form-select form-select-sm">
                                <option value="">Semua Prioritas</option>
                                <option value="Rendah">Rendah</option>
                                <option value="Normal">Normal</option>
                                <option value="Tinggi">Tinggi</option>
                                <option value="Segera">Segera</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button id="btn-filter" class="btn btn-sm btn-info text-white w-100">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                        </div>
                    </div>

                    <!-- DataTable -->
                    <div class="table-responsive">
                        <table id="suratMasukTable" class="table table-hover table-bordered" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Agenda</th>
                                    <th>Tanggal Terima</th>
                                    <th>Pengirim</th>
                                    <th>Perihal</th>
                                    <th>Status</th>
                                    <th>Prioritas</th>
                                    <th width="15%">Aksi</th>
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
    var table = $('#suratMasukTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('surat-masuk.index') }}",
            data: function(d) {
                d.tanggal_mulai = $('#filter-tanggal-mulai').val();
                d.tanggal_sampai = $('#filter-tanggal-sampai').val();
                d.klasifikasi_id = $('#filter-klasifikasi').val();
                d.unit_id = $('#filter-unit').val();
                d.sifat_id = $('#filter-sifat').val();
                d.prioritas = $('#filter-prioritas').val();
                d.status = $('#filter-status').val();
            }
        },
        columns: [
            {data: 'agenda', name: 'agenda'},
            {data: 'tanggal_terima', name: 'tanggal_terima'},
            {data: 'pengirim', name: 'pengirim'},
            {data: 'perihal', name: 'perihal'},
            {data: 'status', name: 'status', orderable: false},
            {data: 'prioritas', name: 'prioritas', orderable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [[1, 'desc']],
        pageLength: 10
    });

    $('#btn-filter').on('click', function() {
        table.ajax.reload();
    });

    // Initialize datepickers
    $('#filter-tanggal-mulai, #filter-tanggal-sampai').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });

    // Edit button handler
    $(document).on('click', '.btn-edit', function() {
        let id = $(this).data('id');
        window.location.href = "{{ route('surat-masuk.edit', ':id') }}".replace(':id', id);
    });

    // Delete button handler
    $(document).on('click', '.btn-delete', function() {
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
                            table.ajax.reload();
                            Swal.fire('Terhapus!', res.message, 'success');
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
