@extends('layouts.app')

@section('title', 'Template Disposisi')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-text"></i> Template Disposisi Saya</h5>
                    <button class="btn btn-primary" onclick="openModal()">
                        <i class="bi bi-plus-circle"></i> Tambah Template
                    </button>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="bi bi-info-circle"></i> Template disposisi hanya dapat dilihat dan diedit oleh pemiliknya.
                    </div>
                    <div class="table-responsive">
                        <table id="templateTable" class="table table-bordered table-hover" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Template</th>
                                    <th>Instruksi Default</th>
                                    <th>Tujuan Default</th>
                                    <th>Tembusan Default</th>
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
<div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="templateForm">
                @csrf
                <input type="hidden" id="templateId" name="_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Template Disposisi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Template <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama" name="nama" required>
                        <small class="text-danger" id="error-nama"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Instruksi Default <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="instruksi_default" name="instruksi_default" rows="3" required></textarea>
                        <small class="text-danger" id="error-instruksi_default"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tujuan Default (Multi Select)</label>
                        <select class="form-select" id="tujuan_default" name="tujuan_default[]" multiple size="4">
                            <!-- Will be populated via AJAX -->
                        </select>
                        <small class="text-muted">Tahan Ctrl/Cmd untuk memilih beberapa user</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tembusan Default</label>
                        <textarea class="form-control" id="tembusan_default" name="tembusan_default" rows="2"></textarea>
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
    var table = $('#templateTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('master.template-disposisi.index') }}",
        columns: [
            { data: 'nama', name: 'nama' },
            { data: 'instruksi_default', name: 'instruksi_default' },
            { 
                data: 'tujuan_default', 
                name: 'tujuan_default',
                render: function(data) {
                    if(!data || data.length === 0) return '-';
                    return data.length + ' user dipilih';
                }
            },
            { data: 'tembusan_default', name: 'tembusan_default' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'asc']]
    });

    // Load users for dropdown
    $.get("{{ route('master.template-disposisi.create') }}", function(data) {
        if(data.users) {
            let options = '';
            data.users.forEach(function(user) {
                options += '<option value="'+user.id+'">'+user.name+' ('+user.email+')</option>';
            });
            $('#tujuan_default').html(options);
        }
    }).fail(function() {
        // Fallback if route doesn't exist
        $('#tujuan_default').html('<option value="">Loading users...</option>');
    });

    $('#templateForm').on('submit', function(e) {
        e.preventDefault();
        let id = $('#templateId').val();
        let url = id ? "{{ route('master.template-disposisi.update', ':id') }}".replace(':id', id) : "{{ route('master.template-disposisi.store') }}";
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
                $('#templateModal').modal('hide');
                table.ajax.reload();
                Swal.fire('Sukses', res.message, 'success');
            },
            error: function(xhr) {
                if(xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    for(let key in errors) {
                        $('#error-'+key).text(errors[key][0]);
                    }
                } else if(xhr.status === 403) {
                    Swal.fire('Gagal', 'Anda tidak memiliki akses ke template ini.', 'error');
                } else {
                    Swal.fire('Gagal', 'Terjadi kesalahan sistem.', 'error');
                }
            }
        });
    });
});

function openModal(id = null) {
    $('#templateForm')[0].reset();
    $('#templateId').val('');
    $('input[name=_method]').val('POST');
    $('#modalTitle').text('Tambah Template Disposisi');

    if(id) {
        $.get("{{ route('master.template-disposisi.show', ':id') }}".replace(':id', id), function(res) {
            $('#templateId').val(res.id);
            $('input[name=_method]').val('PUT');
            $('#modalTitle').text('Edit Template Disposisi');
            $('#nama').val(res.nama);
            $('#instruksi_default').val(res.instruksi_default);
            
            if(res.tujuan_default && Array.isArray(res.tujuan_default)) {
                $('#tujuan_default').val(res.tujuan_default);
            }
            
            $('#tembusan_default').val(res.tembusan_default || '');
            $('#templateModal').modal('show');
        }).fail(function(xhr) {
            if(xhr.status === 403) {
                Swal.fire('Error', 'Anda tidak memiliki akses ke template ini', 'error');
            } else {
                Swal.fire('Error', 'Data tidak ditemukan', 'error');
            }
        });
    } else {
        $('#templateModal').modal('show');
    }
}

$(document).on('click', '.btn-edit', function() {
    openModal($(this).data('id'));
});

$(document).on('click', '.btn-delete', function() {
    let id = $(this).data('id');
    Swal.fire({
        title: 'Yakin hapus?',
        text: "Template akan dihapus permanen",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('master.template-disposisi.destroy', ':id') }}".replace(':id', id),
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    $('#templateTable').DataTable().ajax.reload();
                    Swal.fire('Terhapus!', 'Template berhasil dihapus', 'success');
                },
                error: function(xhr) {
                    if(xhr.status === 403) {
                        Swal.fire('Gagal!', 'Anda tidak memiliki akses ke template ini.', 'error');
                    } else {
                        Swal.fire('Gagal!', 'Terjadi kesalahan.', 'error');
                    }
                }
            });
        }
    });
});
</script>
@endpush
