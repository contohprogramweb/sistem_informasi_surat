@extends('layouts.app')

@section('title', 'Log TTE - Surat Keluar')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-journal-text"></i> Log Tanda Tangan Elektronik</h5>
                    <a href="{{ route('surat-keluar.show', $suratKeluar) }}" class="btn btn-sm btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="bi bi-info-circle"></i> Informasi Surat</h6>
                        <table class="table table-sm table-borderless mb-0">
                            <tr>
                                <td width="150">Nomor Surat</td>
                                <td>: <strong>{{ $suratKeluar->nomor_surat_final ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Perihal</td>
                                <td>: {{ $suratKeluar->perihal }}</td>
                            </tr>
                            <tr>
                                <td>Status</td>
                                <td>: <span class="badge bg-{{ $suratKeluar->status->color() }}">{{ $suratKeluar->status->label() }}</span></td>
                            </tr>
                        </table>
                    </div>

                    @if($logs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="20%">User</th>
                                        <th width="15%">Posisi (X, Y)</th>
                                        <th width="10%">Scale</th>
                                        <th width="25%">Hash File (SHA-256)</th>
                                        <th width="10%">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $index => $log)
                                        <tr class="{{ $log->success ? '' : 'table-danger' }}">
                                            <td>{{ $logs->firstItem() + $index }}</td>
                                            <td>{{ $log->created_at->format('d M Y H:i:s') }}</td>
                                            <td>
                                                <strong>{{ $log->user->name ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $log->ip_address }}</small>
                                            </td>
                                            <td>{{ $log->position_x }}, {{ $log->position_y }}</td>
                                            <td>{{ $log->scale }}x</td>
                                            <td>
                                                @if($log->hash_file)
                                                    <code title="{{ $log->hash_file }}">
                                                        {{ Str::limit($log->hash_file, 20) }}
                                                    </code>
                                                @else
                                                    <span class="text-danger">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($log->success)
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Berhasil
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-x-circle"></i> Gagal
                                                    </span>
                                                    <br>
                                                    <small class="text-danger">{{ Str::limit($log->error_message, 50) }}</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $logs->links() }}
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> Belum ada log TTE untuk surat ini.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
