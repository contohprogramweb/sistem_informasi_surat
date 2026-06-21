@extends('layouts.app')

@section('title', 'Notifikasi Arsip')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Notifikasi Arsip</h1>
                <a href="{{ route('arsip.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Daftar Notifikasi ({{ $notifications->total() }})</h5>
        </div>
        <div class="card-body">
            @if($notifications->count() > 0)
            <div class="list-group">
                @foreach($notifications as $notification)
                @php
                    $arsip = $notification->arsip;
                @endphp
                <div class="list-group-item list-group-item-action {{ !$notification->is_read ? 'list-group-item-info' : '' }}">
                    <div class="d-flex w-100 justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">
                                @if(!$notification->is_read)
                                    <span class="badge bg-primary me-2">Baru</span>
                                @endif
                                {{ $notification->type_label }}
                            </h6>
                            <p class="mb-1">{{ $notification->message }}</p>
                            <small class="text-muted">
                                Dikirim: {{ $notification->sent_at->format('d/m/Y H:i') }}
                                @if($arsip)
                                    | Arsip: {{ $arsip->nomor_surat ?? $arsip->nomor_surat_final ?? 'N/A' }}
                                @endif
                            </small>
                        </div>
                        <div>
                            @if(!$notification->is_read)
                                <form action="{{ route('arsip.notifications.read', $notification->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-check"></i> Tandai Dibaca
                                    </button>
                                </form>
                            @endif
                            @if($arsip)
                                <a href="{{ $notification->arsip_type === App\Models\SuratMasuk::class ? route('surat-masuk.show', $arsip->id) : route('surat-keluar.show', $arsip->id) }}" 
                                   class="btn btn-sm btn-outline-info ms-1">
                                    <i class="fas fa-eye"></i> Lihat
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
            @else
            <p class="text-muted text-center py-4">Tidak ada notifikasi.</p>
            @endif
        </div>
    </div>
</div>
@endsection
