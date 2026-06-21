@extends('layouts.app')

@section('title', 'Berita Acara Pemusnahan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Berita Acara Pemusnahan Arsip</h1>
                <div>
                    <a href="{{ route('arsip.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    @if($readyForDestruction->count() > 0)
                        <a href="{{ route('arsip.berita-acara.create') }}" class="btn btn-danger">
                            <i class="fas fa-plus"></i> Buat Berita Acara
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Info Box -->
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Perhatian:</strong> Hanya arsip yang telah melewati masa retensi inaktif yang dapat dimusnahkan.
        Proses pemusnahan bersifat permanen dan harus dicatat dalam berita acara untuk keperluan audit.
    </div>

    <!-- Arsip Ready for Destruction -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Arsip Siap Dimusnahkan ({{ count($readyForDestruction) }} arsip)</h5>
        </div>
        <div class="card-body">
            @if(count($readyForDestruction) > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipe</th>
                            <th>Nomor Surat</th>
                            <th>Perihal</th>
                            <th>Klasifikasi</th>
                            <th>Tanggal Jatuh Tempo</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($readyForDestruction as $item)
                        @php
                            $model = $item['model'];
                        @endphp
                        <tr>
                            <td>
                                @if($item['type'] === 'surat_masuk')
                                    <span class="badge bg-primary">Surat Masuk</span>
                                @else
                                    <span class="badge bg-success">Surat Keluar</span>
                                @endif
                            </td>
                            <td>{{ $model->nomor_surat ?? $model->nomor_surat_final }}</td>
                            <td>{{ Str::limit($model->perihal, 40) }}</td>
                            <td>{{ $model->klasifikasi?->nama ?? '-' }}</td>
                            <td>{{ $model->tanggal_jatuh_inaktif?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <form action="{{ route('arsip.berita-acara.store') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="arsip_list[0][type]" value="{{ $item['type'] }}">
                                    <input type="hidden" name="arsip_list[0][id]" value="{{ $model->id }}">
                                    <input type="hidden" name="tanggal_berita_acara" value="{{ now()->format('Y-m-d') }}">
                                    <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm('Tambahkan arsip ini ke berita acara pemusnahan?')">
                                        <i class="fas fa-trash"></i> Musnahkan
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-muted text-center py-4">Tidak ada arsip yang siap dimusnahkan.</p>
            @endif
        </div>
    </div>
</div>
@endsection
