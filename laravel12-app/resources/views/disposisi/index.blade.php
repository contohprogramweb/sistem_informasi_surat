@extends('layouts.app')

@section('title', 'Disposisi Masuk')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 text-primary"><i class="bi bi-inbox"></i> Disposisi Masuk</h5>
                        <div>
                            <select id="statusFilter" class="form-select form-select-sm" style="width: 200px; display: inline-block;">
                                <option value="">Semua Status</option>
                                <option value="Belum Dibaca">Belum Dibaca</option>
                                <option value="Sudah Dibaca">Sudah Dibaca</option>
                                <option value="Sedang Ditindaklanjuti">Sedang Ditindaklanjuti</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Belum Selesai">Belum Selesai</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="disposisiTable" class="table table-hover" style="width:100%">
                            <thead class="table-light">
                                <tr>
                                    <th>Informasi Surat</th>
                                    <th>Dari</th>
                                    <th>Prioritas</th>
                                    <th>Batas Waktu</th>
                                    <th>Status</th>
                                    <th width="10%">Aksi</th>
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
    var table = $('#disposisiTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('disposisi.index') }}",
            data: function(d) {
                d.status = $('#statusFilter').val();
            }
        },
        columns: [
            { data: 'surat_info', name: 'surat_masuk_id', orderable: false },
            { data: 'dari', name: 'dariUser.name' },
            { data: 'prioritas_badge', name: 'prioritas', orderable: false },
            { data: 'batas_waktu', name: 'batas_waktu' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[3, 'desc']], // Sort by batas_waktu
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
        },
        drawCallback: function() {
            // Refresh tooltips if any
            $('[data-bs-toggle="tooltip"]').tooltip();
        }
    });

    $('#statusFilter').on('change', function() {
        table.ajax.reload();
    });
});
</script>
@endpush
