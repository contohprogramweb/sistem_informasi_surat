@extends('layouts.app')

@section('title', 'Klasifikasi Arsip')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-folder-tree"></i> Klasifikasi Arsip</h5>
                    <button class="btn btn-primary" onclick="openModal()">
                        <i class="bi bi-plus-circle"></i> Tambah Klasifikasi
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="klasifikasiTable" class="table table-bordered table-hover" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode</th>
                                    <th>Nama Klasifikasi</th>
                                    <th>Induk</th>
                                    <th>Retensi Aktif (Thn)</th>
                                    <th>Retensi Inaktif (Thn)</th>
                                    <th>Status</th>
                                    <th width="15%">Aksi</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Struktur Tree</h6>
                    <div class="tree-view">
                        @include('master.klasifikasi.tree', ['items' => $treeData, 'level' => 0])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Form -->
<div class="modal fade" id="klasifikasiModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="klasifikasiForm">
                @csrf
                <input type="hidden" id="klasifikasiId" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Klasifikasi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Kode Klasifikasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kode" name="kode" required>
                        <small class="text-danger" id="error-kode"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Klasifikasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                        <small class="text-danger" id="error-nama"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Induk (Parent)</label>
                        <select class="form-select" id="parent_id" name="parent_id">
                            <option value="">-- Tidak Ada (Root) --</option>
                        </select>
                        <small class="text-muted">Kosongkan jika ini adalah klasifikasi tingkat atas</small>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Retensi Aktif (Tahun) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="retensi_aktif" name="retensi_aktif" min="1" required>
                            <small class="text-danger" id="error-retensi_aktif"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Retensi Inaktif (Tahun) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="retensi_inaktif" name="retensi_inaktif" min="1" required>
                            <small class="text-danger" id="error-retensi_inaktif"></small>
                        </div>
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
    var table = $('#klasifikasiTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('master.klasifikasi.index') }}",
        columns: [
            { data: 'kode', name: 'kode' },
            { data: 'nama', name: 'nama' },
            { data: 'parent_name', name: 'parent.nama' },
            { data: 'retensi_aktif', name: 'retensi_aktif' },
            { data: 'retensi_inaktif', name: 'retensi_inaktif' },
            { data: 'status', name: 'deleted_at', orderable: false, render: function(data){ return data ? '<span class="badge bg-danger">Terhapus</span>' : '<span class="badge bg-success">Aktif</span>'; }},
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    // Load tree list for dropdown
    $.get("{{ route('master.klasifikasi.tree') }}", function(data) {
        let options = '<option value="">-- Tidak Ada (Root) --</option>';
        data.forEach(function(item) {
            options += '<option value="'+item.id+'">'+item.nama+'</option>';
        });
        $('#parent_id').html(options);
    });

    $('#klasifikasiForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#klasifikasiId').val();
        let url = id ? "{{ route('master.klasifikasi.update', ':id') }}".replace(':id', id) : "{{ route('master.klasifikasi.store') }}";
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
                $('#klasifikasiModal').modal('hide');
                table.ajax.reload();
                Swal.fire('Sukses', res.message, 'success');
                location.reload(); // Reload untuk update tree view
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
    $('#klasifikasiForm')[0].reset();
    $('#klasifikasiId').val('');
    $('input[name=_method]').val('POST');
    $('#modalTitle').text('Tambah Klasifikasi');

    if(id) {
        $.get("{{ route('master.klasifikasi.show', ':id') }}".replace(':id', id), function(res) {
            $('#klasifikasiId').val(res.id);
            $('input[name=_method]').val('PUT');
            $('#modalTitle').text('Edit Klasifikasi');
            $('#kode').val(res.kode);
            $('#nama').val(res.nama);
            $('#parent_id').val(res.parent_id || '');
            $('#retensi_aktif').val(res.retensi_aktif);
            $('#retensi_inaktif').val(res.retensi_inaktif);
            $('#klasifikasiModal').modal('show');
        }).fail(function() {
            Swal.fire('Error', 'Data tidak ditemukan', 'error');
        });
    } else {
        $('#klasifikasiModal').modal('show');
    }
}

$(document).on('click', '.btn-edit', function() {
    openModal($(this).data('id'));
});

$(document).on('click', '.btn-delete', function() {
    let id = $(this).data('id');
    Swal.fire({
        title: 'Yakin hapus?',
        text: "Klasifikasi yang memiliki anak atau surat terkait tidak dapat dihapus",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('master.klasifikasi.destroy', ':id') }}".replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    if(res.success) {
                        $('#klasifikasiTable').DataTable().ajax.reload();
                        Swal.fire('Terhapus!', res.message, 'success');
                        location.reload();
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
        url: "{{ route('master.klasifikasi.restore', ':id') }}".replace(':id', id),
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function() {
            $('#klasifikasiTable').DataTable().ajax.reload();
            Swal.fire('Pulih', 'Data berhasil dipulihkan', 'success');
            location.reload();
        }
    });
});
</script>
@endpush
