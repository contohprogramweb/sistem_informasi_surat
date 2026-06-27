@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Buat Surat Keluar Baru</h1>
        <a href="{{ route('surat-keluar.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Formulir Surat Keluar</h6>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('surat-keluar.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="nomor_surat" class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nomor_surat') is-invalid @enderror" 
                               id="nomor_surat" name="nomor_surat" value="{{ old('nomor_surat') }}" required>
                        @error('nomor_surat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="tanggal_surat" class="form-label">Tanggal Surat <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal_surat') is-invalid @enderror" 
                               id="tanggal_surat" name="tanggal_surat" value="{{ old('tanggal_surat', date('Y-m-d')) }}" required>
                        @error('tanggal_surat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="tujuan" class="form-label">Tujuan Surat <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('tujuan') is-invalid @enderror" 
                           id="tujuan" name="tujuan" value="{{ old('tujuan') }}" required>
                    @error('tujuan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="perihal" class="form-label">Perihal <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('perihal') is-invalid @enderror" 
                              id="perihal" name="perihal" rows="3" required>{{ old('perihal') }}</textarea>
                    @error('perihal')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="unit_id" class="form-label">Unit Pengirim <span class="text-danger">*</span></label>
                        <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id" required>
                            <option value="">Pilih Unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->nama_unit }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="klasifikasi_arsip_id" class="form-label">Klasifikasi Arsip</label>
                        <select class="form-select @error('klasifikasi_arsip_id') is-invalid @enderror" id="klasifikasi_arsip_id" name="klasifikasi_arsip_id">
                            <option value="">Pilih Klasifikasi</option>
                            @foreach($klasifikasis as $klasifikasi)
                                <option value="{{ $klasifikasi->id }}" {{ old('klasifikasi_arsip_id') == $klasifikasi->id ? 'selected' : '' }}>
                                    {{ $klasifikasi->kode }} - {{ $klasifikasi->uraian }}
                                </option>
                            @endforeach
                        </select>
                        @error('klasifikasi_arsip_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="file_scan" class="form-label">File Scan Surat (PDF)</label>
                    <input type="file" class="form-control @error('file_scan') is-invalid @enderror" 
                           id="file_scan" name="file_scan" accept=".pdf">
                    <small class="text-muted">Format: PDF, Maksimal 5MB</small>
                    @error('file_scan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="reset" class="btn btn-warning me-md-2">Reset</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Surat Keluar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
