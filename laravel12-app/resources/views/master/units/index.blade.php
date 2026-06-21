@extends('layouts.app')

@section('title', 'Master Data Unit')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-building"></i> Daftar Unit Kerja</h5>
                    <button class="btn btn-primary" onclick="openModal()">
                        <i class="bi bi-plus-circle"></i> Tambah Unit
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="unitsTable" class="table table-bordered table-hover" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Unit</th>
                                    <th>Nama Unit</th>
                                    <th>Deskripsi</th>
                                    <th>Status</th>
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

<!-- Modal Form -->
<div class="modal fade" id="unitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="unitForm">
                @csrf
                <input type="hidden" id="unitId" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Unit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control text-uppercase" id="kode_unit" name="kode_unit" required style="text-transform:uppercase">
                        <small class="text-danger" id="error-kode_unit"></small>
                        <small class="text-muted">Huruf besar dan angka, 3-10 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Unit <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_unit" name="nama_unit" required>
                        <small class="text-danger" id="error-nama_unit"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    var table = $('#unitsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('master.units.index') }}",
        columns: [
            { data: 'kode_unit', name: 'kode_unit' },
            { data: 'nama_unit', name: 'nama_unit' },
            { data: 'deskripsi', name: 'deskripsi' },
            { data: 'status', name: 'deleted_at', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    $('#unitForm').on('submit', function(e) {
        e.preventDefault();
        let unitId = $('#unitId').val();
        let url = unitId ? "{{ route('master.units.update', ':id') }}".replace(':id', unitId) : "{{ route('master.units.store') }}";
        let method = unitId ? 'PUT' : 'POST';

        $('.text-danger').text('');

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                $('#unitModal').modal('hide');
                table.ajax.reload();
                Swal.fire('Sukses', res.message, 'success');
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    for(let key in errors) {
                        $('#error-'+key).text(errors[key][0]);
                    }
                } else if (xhr.status === 403) {
                    Swal.fire('Gagal', 'Anda tidak memiliki akses.', 'error');
                } else {
                    Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error');
                }
            }
        });
    });
});

function openModal(id = null) {
    $('#unitForm')[0].reset();
    $('#unitId').val('');
    $('input[name=_method]').val('POST');
    $('#modalTitle').text('Tambah Unit');

    if(id) {
        $.get("{{ route('master.units.show', ':id') }}".replace(':id', id), function(res) {
            $('#unitId').val(res.id);
            $('input[name=_method]').val('PUT');
            $('#modalTitle').text('Edit Unit');
            $('#kode_unit').val(res.kode_unit);
            $('#nama_unit').val(res.nama_unit);
            $('#deskripsi').val(res.deskripsi || '');
            $('#unitModal').modal('show');
        }).fail(function() {
            Swal.fire('Error', 'Data tidak ditemukan', 'error');
        });
    } else {
        $('#unitModal').modal('show');
    }
}

$(document).on('click', '.btn-edit', function() {
    openModal($(this).data('id'));
});

$(document).on('click', '.btn-delete', function() {
    let id = $(this).data('id');
    Swal.fire({
        title: 'Yakin hapus?',
        text: "Data akan dihapus sementara (soft delete)",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('master.units.destroy', ':id') }}".replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if(res.success) {
                        $('#unitsTable').DataTable().ajax.reload();
                        Swal.fire('Terhapus!', res.message, 'success');
                    } else {
                        Swal.fire('Gagal!', res.message, 'error');
                    }
                }
            });
        }
    });
});

$(document).on('click', '.btn-restore', function() {
    let id = $(this).data('id');
    $.ajax({
        url: "{{ route('master.units.restore', ':id') }}".replace(':id', id),
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            $('#unitsTable').DataTable().ajax.reload();
            Swal.fire('Pulih', 'Data berhasil dipulihkan', 'success');
        }
    });
});
</script>
@endpush
