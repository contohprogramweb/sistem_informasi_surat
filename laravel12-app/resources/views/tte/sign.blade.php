@extends('layouts.app')

@section('title', 'Tanda Tangan Elektronik - ' . $suratKeluar->nomor_surat_final)

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Kolom Preview PDF -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-file-earmark-pdf"></i> Pratinjau Surat</h5>
                    <span class="badge bg-info">{{ $suratKeluar->status->label() }}</span>
                </div>
                <div class="card-body p-0">
                    <div id="pdf-container" style="height: 700px; overflow: auto; background: #f0f0f0;">
                        <div class="text-center p-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memuat PDF...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kontrol TTE -->
        <div class="col-lg-4">
            @if(!$hasSignature)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Perhatian:</strong> Pimpinan belum memiliki tanda tangan digital.
                    Silakan hubungi Admin TU untuk upload tanda tangan.
                </div>
            @endif

            <div class="card {{ !$hasSignature ? 'opacity-50' : '' }}">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Posisi Tanda Tangan</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Informasi Surat</h6>
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="120">Nomor Surat</td>
                                <td>: <strong>{{ $suratKeluar->nomor_surat_final ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Tanggal</td>
                                <td>: {{ $suratKeluar->tanggal_surat_final?->format('d M Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td>Perihal</td>
                                <td>: {{ Str::limit($suratKeluar->perihal, 30) }}</td>
                            </tr>
                        </table>
                    </div>

                    <hr>

                    <form id="tte-form">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="position_x" class="form-label">Posisi Horizontal (X)</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="position_x" 
                                       name="position_x" 
                                       value="450"
                                       min="0"
                                       required>
                                <span class="input-group-text">px</span>
                            </div>
                            <small class="text-muted">Dari kiri halaman</small>
                        </div>

                        <div class="mb-3">
                            <label for="position_y" class="form-label">Posisi Vertikal (Y)</label>
                            <div class="input-group">
                                <input type="number" 
                                       class="form-control" 
                                       id="position_y" 
                                       name="position_y" 
                                       value="650"
                                       min="0"
                                       required>
                                <span class="input-group-text">px</span>
                            </div>
                            <small class="text-muted">Dari atas halaman</small>
                        </div>

                        <div class="mb-3">
                            <label for="scale" class="form-label">Skala Gambar</label>
                            <div class="input-group">
                                <input type="range" 
                                       class="form-range" 
                                       id="scale" 
                                       name="scale" 
                                       min="0.5" 
                                       max="3" 
                                       step="0.1" 
                                       value="1">
                                <span class="input-group-text" id="scale-value">1.0x</span>
                            </div>
                            
                            <div class="btn-group btn-group-sm mt-2 w-100">
                                <button type="button" class="btn btn-outline-secondary" onclick="setScale(0.7)">Kecil</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setScale(1.0)">Sedang</button>
                                <button type="button" class="btn btn-outline-secondary" onclick="setScale(1.5)">Besar</button>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="button" 
                                    class="btn btn-primary" 
                                    id="btn-sign"
                                    {{ !$hasSignature ? 'disabled' : '' }}
                                    data-bs-toggle="modal" 
                                    data-bs-target="#confirmModal">
                                <i class="bi bi-pencil-square"></i> Tandatangan
                            </button>
                            
                            <a href="{{ route('surat-keluar.show', $suratKeluar) }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Marker posisi di PDF -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-crosshair"></i> Preview Posisi</h6>
                </div>
                <div class="card-body">
                    <div id="position-preview" class="border rounded" style="height: 100px; position: relative; background: #fff;">
                        <div id="signature-marker" 
                             style="position: absolute; width: 100px; height: 50px; border: 2px dashed red; display: flex; align-items: center; justify-content: center; font-size: 10px; color: red;">
                            TTE
                        </div>
                    </div>
                    <small class="text-muted">Klik pada PDF untuk set posisi</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Konfirmasi Tanda Tangan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menandatangani surat ini?</p>
                <ul>
                    <li><strong>Nomor Surat:</strong> {{ $suratKeluar->nomor_surat_final ?? '-' }}</li>
                    <li><strong>Posisi X:</strong> <span id="confirm-x">-</span> px</li>
                    <li><strong>Posisi Y:</strong> <span id="confirm-y">-</span> px</li>
                    <li><strong>Skala:</strong> <span id="confirm-scale">-</span>x</li>
                </ul>
                <p class="text-danger small">
                    <i class="bi bi-info-circle"></i> Setelah ditandatangani, surat tidak dapat diubah kembali.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-warning" id="btn-confirm-sign">
                    <i class="bi bi-check-circle"></i> Ya, Tandatangan
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<!-- PDF.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
// Setup PDF.js worker
pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

let pdfDoc = null;
let currentPage = 1;
const scale = parseFloat(document.getElementById('scale').value);

// Load PDF
async function loadPDF() {
    try {
        const response = await fetch('{{ route("tte.pdf-preview", $suratKeluar) }}');
        const data = new Uint8Array(await response.arrayBuffer());
        
        pdfDoc = await pdfjsLib.getDocument(data).promise;
        renderPage(currentPage);
    } catch (error) {
        console.error('Error loading PDF:', error);
        document.getElementById('pdf-container').innerHTML = 
            '<div class="alert alert-danger m-3">Gagal memuat PDF: ' + error.message + '</div>';
    }
}

async function renderPage(num) {
    const page = await pdfDoc.getPage(num);
    const viewport = page.getViewport({ scale: 1.5 });
    
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    canvas.height = viewport.height;
    canvas.width = viewport.width;
    
    const renderContext = {
        canvasContext: context,
        viewport: viewport
    };
    
    await page.render(renderContext).promise;
    
    canvas.style.display = 'block';
    canvas.style.margin = '10px auto';
    canvas.id = 'pdf-page-' + num;
    canvas.onclick = function(e) {
        const rect = canvas.getBoundingClientRect();
        const x = Math.round((e.clientX - rect.left) * (viewport.width / rect.width));
        const y = Math.round((e.clientY - rect.top) * (viewport.height / rect.height));
        
        document.getElementById('position_x').value = x;
        document.getElementById('position_y').value = y;
        updateMarker(x, y);
    };
    
    document.getElementById('pdf-container').innerHTML = '';
    document.getElementById('pdf-container').appendChild(canvas);
}

// Update marker position
function updateMarker(x, y) {
    const marker = document.getElementById('signature-marker');
    marker.style.left = (x - 50) + 'px';
    marker.style.top = (y - 25) + 'px';
}

// Set scale preset
function setValue(s) {
    document.getElementById('scale').value = s;
    document.getElementById('scale-value').textContent = s.toFixed(1) + 'x';
}

// Handle scale change
document.getElementById('scale').addEventListener('input', function() {
    document.getElementById('scale-value').textContent = this.value + 'x';
});

// Show confirmation modal
document.getElementById('btn-confirm-sign').addEventListener('click', function() {
    const formData = {
        position_x: document.getElementById('position_x').value,
        position_y: document.getElementById('position_y').value,
        scale: document.getElementById('scale').value,
        _token: '{{ csrf_token() }}'
    };

    // Update confirmation values
    document.getElementById('confirm-x').textContent = formData.position_x;
    document.getElementById('confirm-y').textContent = formData.position_y;
    document.getElementById('confirm-scale').textContent = formData.scale;

    // Close modal and submit
    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
    modal.hide();

    // Show loading
    document.getElementById('btn-sign').disabled = true;
    document.getElementById('btn-sign').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Memproses...';

    // Submit via AJAX
    fetch('{{ route("tte.sign-document", $suratKeluar) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': formData._token
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Surat berhasil ditandatangani.',
                timer: 2000
            }).then(() => {
                window.location.href = data.redirect;
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: data.message
            });
            document.getElementById('btn-sign').disabled = false;
            document.getElementById('btn-sign').innerHTML = '<i class="bi bi-pencil-square"></i> Tandatangan';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'Terjadi kesalahan saat menandatangani surat.'
        });
        document.getElementById('btn-sign').disabled = false;
        document.getElementById('btn-sign').innerHTML = '<i class="bi bi-pencil-square"></i> Tandatangan';
    });
});

// Initialize
loadPDF();
updateMarker(450, 650);
</script>
@endpush
@endsection
