@extends('layouts.app')

@section('title', 'Detail Disposisi')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Kolom Kiri: Detail Disposisi -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="bi bi-file-text"></i> Detail Disposisi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Dari:</th>
                            <td>{{ $disposisi->dariUser->name }}</td>
                        </tr>
                        <tr>
                            <th>Kepada:</th>
                            <td>{{ $disposisi->keUser->name }}</td>
                        </tr>
                        <tr>
                            <th>Prioritas:</th>
                            <td>
                                <span class="badge bg-{{ $disposisi->prioritas === 'Segera' ? 'danger' : ($disposisi->prioritas === 'Tinggi' ? 'warning' : 'info') }}">
                                    {{ $disposisi->prioritas }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Batas Waktu:</th>
                            <td class="{{ $disposisi->isOverdue() ? 'text-danger fw-bold' : '' }}">
                                {{ $disposisi->batas_waktu?->format('d M Y') ?? '-' }}
                                @if($disposisi->isOverdue())
                                    <br><small class="text-danger">(Terlambat)</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Status:</th>
                            <td>
                                <span class="badge bg-{{ $disposisi->status_color }}">
                                    {{ $disposisi->status }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Dibaca:</th>
                            <td>
                                @if($disposisi->read_at)
                                    <span class="text-success"><i class="bi bi-check-circle"></i> {{ $disposisi->read_at->diffForHumans() }}</span>
                                @else
                                    <span class="text-muted">Belum dibaca</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Diterima:</th>
                            <td>{{ $disposisi->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    </table>
                    
                    <hr>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Instruksi:</label>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($disposisi->instruksi)) !!}
                        </div>
                    </div>
                    
                    @if($disposisi->komentar_selesai)
                    <div class="mb-3">
                        <label class="form-label fw-bold">Komentar Selesai:</label>
                        <div class="border rounded p-3 bg-light">
                            {!! nl2br(e($disposisi->komentar_selesai)) !!}
                        </div>
                    </div>
                    @endif
                    
                    @if($disposisi->file_tindak_lanjut)
                    <div class="mb-3">
                        <label class="form-label fw-bold">File Tindak Lanjut:</label>
                        <div>
                            <a href="{{ Storage::url($disposisi->file_tindak_lanjut) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i> Download File
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Aksi untuk penerima disposisi -->
            @if($disposisi->ke_user_id === Auth::id() && $disposisi->status !== 'Selesai')
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-warning">
                    <h6 class="mb-0"><i class="bi bi-gear"></i> Tindak Lanjuti</h6>
                </div>
                <div class="card-body">
                    <form id="statusForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Update Status:</label>
                            <select name="status" id="statusSelect" class="form-select" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="Sedang Ditindaklanjuti">Sedang Ditindaklanjuti</option>
                                <option value="Selesai">Selesai</option>
                                <option value="Belum Selesai">Belum Selesai (dengan alasan)</option>
                            </select>
                        </div>
                        
                        <div id="komentarField" class="mb-3 d-none">
                            <label class="form-label">Komentar/Alasan:</label>
                            <textarea name="komentar" id="komentar" class="form-control" rows="3"></textarea>
                        </div>
                        
                        <div id="fileField" class="mb-3 d-none">
                            <label class="form-label">Upload File Tindak Lanjut:</label>
                            <input type="file" name="file_tindak_lanjut" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Max 10MB</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>
            @endif
            
            <!-- Forward Disposisi (untuk Kabag) -->
            @if($disposisi->ke_user_id === Auth::id() && Auth::user()->can('disposisi.forward'))
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="bi bi-share"></i> Teruskan Disposisi</h6>
                </div>
                <div class="card-body">
                    <form id="forwardForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Kepada:</label>
                            <select name="ke_user_id" class="form-select" required>
                                <option value="">-- Pilih Penerima --</option>
                                @foreach(\App\Models\User::where('unit_id', Auth::user()->unit_id)->where('id', '!=', Auth::id())->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Instruksi Tambahan:</label>
                            <textarea name="instruksi" class="form-control" rows="3" required></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Batas Waktu:</label>
                            <input type="date" name="batas_waktu" class="form-control" min="{{ date('Y-m-d') }}">
                        </div>
                        
                        <button type="submit" class="btn btn-info text-white w-100">
                            <i class="bi bi-send"></i> Teruskan
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
        
        <!-- Kolom Kanan: Timeline -->
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-diagram-3"></i> Timeline Disposisi</h6>
                </div>
                <div class="card-body">
                    <div class="timeline" id="timelineContainer">
                        @include('disposisi._timeline_nodes', ['nodes' => $timeline, 'level' => 0])
                    </div>
                </div>
            </div>
            
            <!-- Surat Terkait -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-envelope"></i> Surat Terkait</h6>
                </div>
                <div class="card-body">
                    <a href="{{ route('surat-masuk.show', $disposisi->suratMasuk->id) }}" class="btn btn-outline-primary">
                        <i class="bi bi-eye"></i> Lihat Detail Surat Masuk
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-node {
    position: relative;
    padding-left: 50px;
    margin-bottom: 30px;
}

.timeline-node::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: -30px;
    width: 2px;
    background: #e9ecef;
}

.timeline-node:last-child::before {
    bottom: 0;
}

.timeline-icon {
    position: absolute;
    left: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    z-index: 1;
}

.timeline-content {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #0d6efd;
}

.timeline-level-1 { margin-left: 30px; }
.timeline-level-2 { margin-left: 60px; }
.timeline-level-3 { margin-left: 90px; }
</style>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle field berdasarkan status
    $('#statusSelect').on('change', function() {
        var status = $(this).val();
        
        if (status === 'Selesai') {
            $('#komentarField').removeClass('d-none');
            $('#fileField').removeClass('d-none');
            $('#komentar').attr('required', false);
        } else if (status === 'Belum Selesai') {
            $('#komentarField').removeClass('d-none');
            $('#fileField').addClass('d-none');
            $('#komentar').attr('required', true);
        } else {
            $('#komentarField').addClass('d-none');
            $('#fileField').addClass('d-none');
            $('#komentar').val('').attr('required', false);
        }
    });
    
    // Submit update status
    $('#statusForm').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('_method', 'PUT');
        
        $.ajax({
            url: "{{ route('disposisi.update-status', $disposisi->id) }}",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                Swal.fire('Sukses', res.message, 'success').then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
            }
        });
    });
    
    // Submit forward disposisi
    $('#forwardForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: "{{ route('disposisi.forward', $disposisi->id) }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                Swal.fire('Sukses', res.message, 'success').then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                Swal.fire('Gagal', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
            }
        });
    });
});
</script>
@endpush
