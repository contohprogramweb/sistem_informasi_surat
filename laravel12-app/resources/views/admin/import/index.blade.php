@extends('layouts.app-bootstrap')

@section('title', 'Import Data')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-upload me-2"></i>Import Data</h2>
            <p class="text-muted">Upload data massal dari file Excel/CSV</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Upload Form -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Upload File</h6>
                </div>
                <div class="card-body">
                    <form id="importForm" action="{{ route('admin.import.process') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Tipe Data</label>
                            <select name="type" id="typeSelect" class="form-select" required>
                                <option value="">Pilih Tipe...</option>
                                <option value="surat_masuk">Surat Masuk</option>
                                <option value="surat_keluar">Surat Keluar</option>
                                <option value="users">Pengguna</option>
                                <option value="klasifikasi">Klasifikasi</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">File (Excel/CSV)</label>
                            <input type="file" name="file" id="fileInput" class="form-control" 
                                   accept=".csv,.xlsx,.xls" required>
                            <small class="text-muted">Maksimal 10 MB</small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" id="previewBtn" class="btn btn-outline-primary">
                                <i class="bi bi-eye me-1"></i>Preview Data
                            </button>
                            <button type="submit" class="btn btn-primary" id="importBtn" disabled>
                                <i class="bi bi-check-lg me-1"></i>Import Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Download Templates -->
            <div class="card mt-3">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-file-earmark-arrow-down me-2"></i>Download Template</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Template Surat Masuk
                            <span class="badge bg-secondary">.xlsx</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Template Surat Keluar
                            <span class="badge bg-secondary">.xlsx</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            Template Pengguna
                            <span class="badge bg-secondary">.xlsx</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Panel -->
        <div class="col-lg-6">
            <div class="card" id="previewCard" style="display: none;">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-table me-2"></i>Preview Data</h6>
                </div>
                <div class="card-body">
                    <div id="previewInfo" class="mb-3"></div>
                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                        <table class="table table-sm table-bordered" id="previewTable">
                            <thead id="previewHeader"></thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                    <div class="alert alert-warning mt-3 mb-0">
                        <small>
                            <i class="bi bi-info-circle me-1"></i>
                            Hanya menampilkan 10 baris pertama. Baris yang tidak valid akan dilewati saat import.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Import History Link -->
    <div class="mt-4">
        <a href="{{ route('admin.import.history') }}" class="btn btn-outline-secondary">
            <i class="bi bi-clock-history me-1"></i>Lihat Riwayat Import
        </a>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    const previewBtn = $('#previewBtn');
    const importBtn = $('#importBtn');
    const previewCard = $('#previewCard');
    
    // Preview file
    previewBtn.click(function() {
        const formData = new FormData();
        const fileInput = document.getElementById('fileInput');
        const typeSelect = document.getElementById('typeSelect');
        
        if (!fileInput.files[0] || !typeSelect.value) {
            alert('Pilih tipe data dan upload file terlebih dahulu');
            return;
        }
        
        formData.append('file', fileInput.files[0]);
        formData.append('type', typeSelect.value);
        formData.append('_token', '{{ csrf_token() }}');
        
        previewBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i>Loading...');
        
        $.ajax({
            url: '{{ route("admin.import.preview") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                previewBtn.prop('disabled', false).html('<i class="bi bi-eye me-1"></i>Preview Data');
                
                if (response.success) {
                    // Show preview
                    previewCard.show();
                    importBtn.prop('disabled', false);
                    
                    // Update info
                    $('#previewInfo').html(`
                        <div class="alert alert-success mb-2">
                            <i class="bi bi-check-circle me-1"></i>
                            File valid. Total baris: <strong>${response.total_rows || 0}</strong>
                        </div>
                    `);
                    
                    // Render header
                    let headerHtml = '<tr>';
                    response.headers.forEach(h => {
                        headerHtml += `<th class="bg-light">${h}</th>`;
                    });
                    headerHtml += '</tr>';
                    $('#previewHeader').html(headerHtml);
                    
                    // Render body
                    let bodyHtml = '';
                    response.preview.forEach(row => {
                        bodyHtml += '<tr>';
                        response.headers.forEach(h => {
                            bodyHtml += `<td>${row[h] || ''}</td>`;
                        });
                        bodyHtml += '</tr>';
                    });
                    $('#previewBody').html(bodyHtml);
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function(xhr) {
                previewBtn.prop('disabled', false).html('<i class="bi bi-eye me-1"></i>Preview Data');
                alert('Gagal preview: ' + xhr.responseJSON?.error || 'Terjadi kesalahan');
            }
        });
    });
});
</script>
@endsection
