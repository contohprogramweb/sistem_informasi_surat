@extends('layouts.app-bootstrap')

@section('title', 'Error Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-bug me-2"></i>Error Logs
                </h2>
                <div>
                    <a href="{{ route('admin.audit-trail') }}" class="btn btn-outline-primary">
                        <i class="bi bi-shield-check me-1"></i>Audit Trail
                    </a>
                    <button onclick="location.reload()" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info d-flex align-items-center">
        <i class="bi bi-info-circle-fill me-2 fs-5"></i>
        <div>
            Menampilkan <strong>{{ count($errors) }}</strong> error terakhir dari <code>{{ str_replace(base_path(), '', $logFile) }}</code>
        </div>
    </div>

    <!-- Error List -->
    @if(count($errors) > 0)
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th width="18%">Timestamp</th>
                                <th width="10%">Level</th>
                                <th>Pesan Error</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($errors as $error)
                                <tr>
                                    <td>
                                        <small class="text-muted">{{ $error['timestamp'] }}</small>
                                    </td>
                                    <td>
                                        @if($error['level'] === 'CRITICAL' || $error['level'] === 'ERROR')
                                            <span class="badge bg-danger">{{ $error['level'] }}</span>
                                        @elseif($error['level'] === 'WARNING')
                                            <span class="badge bg-warning text-dark">{{ $error['level'] }}</span>
                                        @else
                                            <span class="badge bg-info">{{ $error['level'] }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <details>
                                            <summary class="cursor-pointer text-primary">
                                                {{ Str::limit($error['message'], 100) }}
                                            </summary>
                                            @if(count($error['trace']) > 0)
                                                <pre class="bg-light p-3 mt-2 rounded small overflow-auto" style="max-height: 300px;">{{ implode("\n", array_slice($error['trace'], 0, 20)) }}</pre>
                                            @endif
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle display-4 text-success"></i>
                <h5 class="mt-3">Tidak ada error</h5>
                <p class="text-muted">Sistem berjalan dengan baik</p>
            </div>
        </div>
    @endif

    <!-- Log File Info -->
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-file-text me-2"></i>Informasi Log</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1"><strong>Lokasi File:</strong></p>
                    <code>{{ $logFile }}</code>
                </div>
                <div class="col-md-6">
                    <p class="mb-1"><strong>Ukuran Baris:</strong></p>
                    <span>{{ number_format($lines) }} baris terakhir</span>
                </div>
            </div>
            <hr>
            <div class="alert alert-warning mb-0">
                <small>
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    <strong>Catatan:</strong> Log file akan di-rotate otomatis. Error log disimpan di <code>storage/logs/error.log</code>.
                    Untuk konfigurasi retention, lihat <code>config/logging.php</code>.
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    details summary { cursor: pointer; }
    details summary:hover { color: #0d6efd !important; }
    pre { white-space: pre-wrap; word-break: break-word; }
</style>
@endsection
