@extends('layouts.app')

@section('title', 'Sifat Surat')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-tag"></i> Sifat Surat</h5>
                    <button class="btn btn-primary" onclick="openModal()">
                        <i class="bi bi-plus-circle"></i> Tambah Sifat
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="sifatTable" class="table table-bordered table-hover" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Sifat</th>
                                    <th>Keterangan</th>
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
<div class="modal fade" id="sifatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="sifatForm">
                @csrf
                <input type="hidden" id="sifatId" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Sifat Surat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Sifat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                        <small class="text-danger" id="error-nama"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Keterangan</label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3"></textarea>
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
    var table = $('#sifatTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('master.sifat-surat.index') }}",
        columns: [
            { data: 'nama', name: 'nama' },
            { data: 'keterangan', name: 'keterangan' },
            { data: 'status', name: 'deleted_at', orderable: false, render: function(data){ return data ? '<span class="badge bg-danger">Terhapus</span>' : '<span class="badge bg-success">Aktif</span>'; }},
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    $('#sifatForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#sifatId').val();
        let url = id ? "{{ route('master.sifat-surat.update', ':id') }}".replace(':id', id) : "{{ route('master.sifat-surat.store') }}";
        let method = id ? 'PUT' : 'POST';

        $('.text-danger').text('');

        $.ajax({
            url: url,
            method: method,
            data: $(this).serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(res) {
                $('#sifatModal').modal('hide');
                table.ajax.reload();
                Swal.fire('Sukses', 'Data berhasil disimpan', 'success');
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    for(let key in errors) {
                        $('#error-'+key).text(errors[key][0]);
                    }
                } else {
                    Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error');
                }
            }
        });
    });
});

function openModal(id = null) {
    $('#sifatForm')[0].reset();
    $('#sifatId').val('');
    $('input[name=_method]').val('POST');
    $('#modalTitle').text('Tambah Sifat Surat');

    if(id) {
        $.get("{{ route('master.sifat-surat.show', ':id') }}".replace(':id', id), function(res) {
            $('#sifatId').val(res.id);
            $('input[name=_method]').val('PUT');
            $('#modalTitle').text('Edit Sifat Surat');
            $('#nama').val(res.nama);
            $('#keterangan').val(res.keterangan || '');
            $('#sifatModal').modal('show');
        }).fail(function() {
            Swal.fire('Error', 'Data tidak ditemukan', 'error');
        });
    } else {
        $('#sifatModal').modal('show');
    }
}

$(document).on('click', '.btn-edit', function() {
    openModal($(this).data('id'));
});

$(document).on('click', '.btn-delete', function() {
    let id = $(this).data('id');
    Swal.fire({
        title: 'Yakin hapus?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('master.sifat-surat.destroy', ':id') }}".replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    $('#sifatTable').DataTable().ajax.reload();
                    Swal.fire('Terhapus!', 'Data berhasil dihapus', 'success');
                }
            });
        }
    });
});

$(document).on('click', '.btn-restore', function() {
    let id = $(this).data('id');
    $.ajax({
        url: "{{ route('master.sifat-surat.restore', ':id') }}".replace(':id', id),
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            $('#sifatTable').DataTable().ajax.reload();
            Swal.fire('Pulih', 'Data berhasil dipulihkan', 'success');
        }
    });
});
</script>
@endpush
