@extends('layouts.app')

@section('title', 'Upload Tanda Tangan Digital')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Upload Tanda Tangan Digital</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if($signature && $signature->is_active)
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Tanda Tangan Aktif</h6>
                            <p class="mb-1"><strong>File:</strong> {{ $signature->original_filename }}</p>
                            <p class="mb-1"><strong>Ukuran:</strong> {{ number_format($signature->file_size / 1024, 2) }} KB</p>
                            <p class="mb-1"><strong>Diupload:</strong> {{ $signature->created_at->format('d M Y H:i') }}</p>
                            @if($signature->expires_at)
                                <p class="mb-0"><strong>Kadaluarsa:</strong> {{ $signature->expires_at->format('d M Y H:i') }}</p>
                            @endif
                        </div>
                    @endif

                    <form action="{{ route('tte.upload-signature.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="signature_image" class="form-label">
                                <i class="bi bi-image"></i> File Tanda Tangan (PNG Transparan)
                            </label>
                            <input type="file" 
                                   class="form-control @error('signature_image') is-invalid @enderror" 
                                   id="signature_image" 
                                   name="signature_image"
                                   accept="image/png"
                                   required>
                            <div class="form-text">
                                <ul class="mb-0">
                                    <li>Format: PNG dengan latar transparan</li>
                                    <li>Ukuran maksimal: 2 MB</li>
                                    <li>File akan dienkripsi menggunakan AES-256</li>
                                </ul>
                            </div>
                            @error('signature_image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Preview</label>
                            <div id="preview-container" class="border rounded p-3 text-center" style="min-height: 150px; background: #f8f9fa;">
                                <p class="text-muted">Preview akan muncul setelah memilih file</p>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload"></i> Upload & Enkripsi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('signature_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('preview-container').innerHTML = 
                '<img src="' + event.target.result + '" alt="Preview" style="max-width: 300px; max-height: 150px;">';
        };
        reader.readAsDataURL(file);
    }
});
</script>
@endpush
@endsection
