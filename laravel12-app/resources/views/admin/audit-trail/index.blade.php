@extends('layouts.app-bootstrap')

@section('title', 'Audit Trail Log')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">
                    <i class="bi bi-shield-check me-2"></i>Audit Trail Log
                </h2>
                <div>
                    <a href="{{ route('admin.error-logs') }}" class="btn btn-outline-warning">
                        <i class="bi bi-bug me-1"></i>Error Logs
                    </a>
                    <button onclick="window.print()" class="btn btn-outline-secondary">
                        <i class="bi bi-printer me-1"></i>Cetak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h6 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-trail') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select select2">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->role }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Entity</label>
                    <select name="entity" class="form-select select2">
                        <option value="">Semua Entity</option>
                        @foreach($entities as $entity)
                            <option value="{{ $entity }}" {{ request('entity') == $entity ? 'selected' : '' }}>
                                {{ $entity }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Aksi</label>
                    <select name="action" class="form-select">
                        <option value="">Semua Aksi</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Dibuat</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Diupdate</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Dihapus</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.audit-trail') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-1"></i>Reset
                    </a>
                    <div class="float-end">
                        <a href="{{ route('admin.audit-trail.export', array_merge(request()->all(), ['format' => 'excel'])) }}" class="btn btn-success btn-sm">
                            <i class="bi bi-file-earmark-excel me-1"></i>Excel
                        </a>
                        <a href="{{ route('admin.audit-trail.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="btn btn-info btn-sm text-white">
                            <i class="bi bi-file-earmark-text me-1"></i>CSV
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th width="15%">Tanggal/Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Entity</th>
                        <th>ID</th>
                        <th>Perubahan</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>
                                <small>{{ $log->created_at->format('d/m/Y H:i:s') }}</small>
                            </td>
                            <td>
                                <strong>{{ $log->user?->name ?? 'System' }}</strong><br>
                                <small class="text-muted">{{ $log->user?->role ?? '-' }}</small>
                            </td>
                            <td>
                                @if($log->action === 'created')
                                    <span class="badge bg-success">Dibuat</span>
                                @elseif($log->action === 'updated')
                                    <span class="badge bg-warning text-dark">Diupdate</span>
                                @elseif($log->action === 'deleted')
                                    <span class="badge bg-danger">Dihapus</span>
                                @endif
                            </td>
                            <td>{{ class_basename($log->entity) }}</td>
                            <td><code>#{{ $log->entity_id }}</code></td>
                            <td>
                                @if($log->diff)
                                    <button class="btn btn-sm btn-outline-primary" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#diffModal{{ $log->id }}">
                                        <i class="bi bi-eye"></i> Lihat Detail
                                    </button>
                                    
                                    <!-- Diff Modal -->
                                    <div class="modal fade" id="diffModal{{ $log->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Detail Perubahan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <h6>{{ $log->description }}</h6>
                                                    <hr>
                                                    @if($log->action === 'deleted')
                                                        <div class="alert alert-danger">Data dihapus</div>
                                                        <pre class="bg-light p-3 rounded">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @elseif($log->action === 'created')
                                                        <div class="alert alert-success">Data baru dibuat</div>
                                                        <pre class="bg-light p-3 rounded">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                    @else
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>Field</th>
                                                                    <th>Sebelum</th>
                                                                    <th>Sesudah</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($log->diff as $field => $change)
                                                                    <tr>
                                                                        <td><strong>{{ $field }}</strong></td>
                                                                        <td class="text-danger">{{ is_null($change['old']) ? '<em>null</em>' : $change['old'] }}</td>
                                                                        <td class="text-success">{{ is_null($change['new']) ? '<em>null</em>' : $change['new'] }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td><small>{{ $log->ip_address }}</small></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="mt-3">Belum ada data audit trail</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
            <div class="card-footer">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
    .table td { vertical-align: middle; }
    .badge { min-width: 80px; }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: 'Pilih...',
            allowClear: true
        });
    });
</script>
@endsection
