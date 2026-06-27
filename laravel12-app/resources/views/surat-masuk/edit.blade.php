@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-pencil"></i> Edit Surat Masuk</h5>
                </div>
                <div class="card-body">
                    <form id="formSuratMasuk" action="{{ route('surat-masuk.update', $suratMasuk->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tanggal_terima" class="form-label">Tanggal Terima <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_terima" id="tanggal_terima" class="form-control @error('tanggal_terima') is-invalid @enderror" value="{{ old('tanggal_terima', $suratMasuk->tanggal_terima->format('Y-m-d')) }}" required>
                                @error('tanggal_terima')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="cara_terima" class="form-label">Cara Terima <span class="text-danger">*</span></label>
                                <select name="cara_terima" id="cara_terima" class="form-select @error('cara_terima') is-invalid @enderror" required>
                                    <option value="">Pilih Cara Terima</option>
                                    <option value="Langsung" {{ old('cara_terima', $suratMasuk->cara_terima) == 'Langsung' ? 'selected' : '' }}>Langsung</option>
                                    <option value="Pos" {{ old('cara_terima', $suratMasuk->cara_terima) == 'Pos' ? 'selected' : '' }}>Pos</option>
                                    <option value="Email" {{ old('cara_terima', $suratMasuk->cara_terima) == 'Email' ? 'selected' : '' }}>Email</option>
                                    <option value="Faksimili" {{ old('cara_terima', $suratMasuk->cara_terima) == 'Faksimili' ? 'selected' : '' }}>Faksimili</option>
                                    <option value="E-Kantor" {{ old('cara_terima', $suratMasuk->cara_terima) == 'E-Kantor' ? 'selected' : '' }}>E-Kantor</option>
                                </select>
                                @error('cara_terima')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="penerima_fisik" class="form-label">Penerima Fisik <span class="text-danger">*</span></label>
                                <input type="text" name="penerima_fisik" id="penerima_fisik" class="form-control @error('penerima_fisik') is-invalid @enderror" value="{{ old('penerima_fisik', $suratMasuk->penerima_fisik) }}" placeholder="Nama penerima fisik surat" required>
                                @error('penerima_fisik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="nomor_surat" class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                                <input type="text" name="nomor_surat" id="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror" value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}" placeholder="Nomor surat dari pengirim" required>
                                @error('nomor_surat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tanggal_surat" class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_surat" id="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror" value="{{ old('tanggal_surat', $suratMasuk->tanggal_surat->format('Y-m-d')) }}" required>
                                @error('tanggal_surat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="pengirim" class="form-label">Pengirim <span class="text-danger">*</span></label>
                                <input type="text" name="pengirim" id="pengirim" class="form-control @error('pengirim') is-invalid @enderror" value="{{ old('pengirim', $suratMasuk->pengirim) }}" placeholder="Instansi/Perorangan pengirim" required>
                                @error('pengirim')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="perihal" class="form-label">Perihal <span class="text-danger">*</span></label>
                                <textarea name="perihal" id="perihal" class="form-control @error('perihal') is-invalid @enderror" rows="3" placeholder="Perihal surat" required>{{ old('perihal', $suratMasuk->perihal) }}</textarea>
                                @error('perihal')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="ringkasan" class="form-label">Ringkasan Isi</label>
                                <textarea name="ringkasan" id="ringkasan" class="form-control @error('ringkasan') is-invalid @enderror" rows="3" placeholder="Ringkasan isi surat">{{ old('ringkasan', $suratMasuk->ringkasan) }}</textarea>
                                @error('ringkasan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="klasifikasi_id" class="form-label">Klasifikasi <span class="text-danger">*</span></label>
                                <select name="klasifikasi_id" id="klasifikasi_id" class="form-select @error('klasifikasi_id') is-invalid @enderror" required>
                                    <option value="">Pilih Klasifikasi</option>
                                    @foreach($klasifikasis as $klas)
                                        <option value="{{ $klas->id }}" {{ old('klasifikasi_id', $suratMasuk->klasifikasi_id) == $klas->id ? 'selected' : '' }}>{{ $klas->nama }}</option>
                                    @endforeach
                                </select>
                                @error('klasifikasi_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="sifat_id" class="form-label">Sifat Surat <span class="text-danger">*</span></label>
                                <select name="sifat_id" id="sifat_id" class="form-select @error('sifat_id') is-invalid @enderror" required>
                                    <option value="">Pilih Sifat</option>
                                    @foreach($sifatSurats as $sifat)
                                        <option value="{{ $sifat->id }}" {{ old('sifat_id', $suratMasuk->sifat_id) == $sifat->id ? 'selected' : '' }}>{{ $sifat->nama }}</option>
                                    @endforeach
                                </select>
                                @error('sifat_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="prioritas" class="form-label">Prioritas <span class="text-danger">*</span></label>
                                <select name="prioritas" id="prioritas" class="form-select @error('prioritas') is-invalid @enderror" required>
                                    <option value="">Pilih Prioritas</option>
                                    <option value="Rendah" {{ old('prioritas', $suratMasuk->prioritas) == 'Rendah' ? 'selected' : '' }}>Rendah</option>
                                    <option value="Normal" {{ old('prioritas', $suratMasuk->prioritas) == 'Normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="Tinggi" {{ old('prioritas', $suratMasuk->prioritas) == 'Tinggi' ? 'selected' : '' }}>Tinggi</option>
                                    <option value="Segera" {{ old('prioritas', $suratMasuk->prioritas) == 'Segera' ? 'selected' : '' }}>Segera</option>
                                </select>
                                @error('prioritas')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="unit_tujuan" class="form-label">Unit Tujuan <span class="text-danger">*</span></label>
                                <select name="unit_tujuan[]" id="unit_tujuan" class="form-select @error('unit_tujuan') is-invalid @enderror" multiple required style="height: 150px;">
                                    @foreach($units as $unit)
                                        <option value="{{ $unit->id }}" {{ in_array($unit->id, old('unit_tujuan', $suratMasuk->unitTujuan->pluck('id')->toArray())) ? 'selected' : '' }}>{{ $unit->nama_unit }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd untuk memilih beberapa unit</small>
                                @error('unit_tujuan')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="tidak_perlu_disposisi" id="tidak_perlu_disposisi" value="1" {{ old('tidak_perlu_disposisi', $suratMasuk->tidak_perlu_disposisi) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="tidak_perlu_disposisi">
                                        Tidak Perlu Disposisi
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="lampiran" class="form-label">Tambah Lampiran File</label>
                                <input type="file" name="lampiran[]" id="lampiran" class="form-control @error('lampiran.*') is-invalid @enderror" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                                <small class="text-muted">Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG. Maksimal 5MB per file.</small>
                                @error('lampiran.*')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                
                                @if($suratMasuk->lampiran->count() > 0)
                                    <div class="mt-3">
                                        <label class="form-label">Lampiran Existing:</label>
                                        <ul class="list-group">
                                            @foreach($suratMasuk->lampiran as $lampiran)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <a href="{{ Storage::url($lampiran->filename) }}" target="_blank">
                                                        <i class="bi bi-file-earmark"></i> {{ $lampiran->original_name }}
                                                    </a>
                                                    <span class="badge bg-secondary">{{ round($lampiran->size / 1024, 2) }} KB</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('surat-masuk.index') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('#formSuratMasuk').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        $.ajax({
            url: $(this).attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            beforeSend: function() {
                $('button[type="submit"]').prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Menyimpan...');
            },
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message
                    }).then(() => {
                        window.location.href = res.redirect;
                    });
                } else {
                    Swal.fire('Gagal!', res.message, 'error');
                    $('button[type="submit"]').prop('disabled', false).html('<i class="bi bi-save"></i> Update');
                }
            },
            error: function(xhr) {
                let message = 'Terjadi kesalahan sistem';
                if(xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if(xhr.responseJSON && xhr.responseJSON.errors) {
                    let errors = '';
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        errors += value[0] + '<br>';
                    });
                    message = errors;
                }
                Swal.fire('Gagal!', message, 'error');
                $('button[type="submit"]').prop('disabled', false).html('<i class="bi bi-save"></i> Update');
            }
        });
    });
});
</script>
@endpush
