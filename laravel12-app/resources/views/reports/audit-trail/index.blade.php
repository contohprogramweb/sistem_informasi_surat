@extends('layouts.app-bootstrap')

@section('title', 'Audit Trail Log')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h3><i class="bi bi-shield-lock"></i> Audit Trail Log</h3>
            <p class="text-muted">Log aktivitas sistem {{ $tanggalMulai }} s/d {{ $tanggalSampai }}</p>
        </div>
        <div class="col-md-4 text-end">
            <div class="btn-group">
                <a href="{{ route('reports.export.audit-trail', ['format' => 'excel', 'tanggal_mulai' => $tanggalMulai, 'tanggal_sampai' => $tanggalSampai, 'user_id' => $userId, 'entity' => $entity, 'action' => $action]) }}" 
                   class="btn btn-success">
                    <i class="bi bi-file-excel"></i> Excel
                </a>
                <a href="{{ route('reports.export.audit-trail', ['format' => 'csv', 'tanggal_mulai' => $tanggalMulai, 'tanggal_sampai' => $tanggalSampai, 'user_id' => $userId, 'entity' => $entity, 'action' => $action]) }}" 
                   class="btn btn-outline-success">
                    <i class="bi bi-filetype-csv"></i> CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ route('reports.audit-trail') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">User</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua User</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $userId == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Entity</label>
                    <input type="text" name="entity" class="form-control" placeholder="Contoh: SuratMasuk" value="{{ $entity }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Aksi</label>
                    <select name="action" class="form-select">
                        <option value="">Semua Aksi</option>
                        <option value="create" {{ $action == 'create' ? 'selected' : '' }}>Create</option>
                        <option value="update" {{ $action == 'update' ? 'selected' : '' }}>Update</option>
                        <option value="delete" {{ $action == 'delete' ? 'selected' : '' }}>Delete</option>
                        <option value="restore" {{ $action == 'restore' ? 'selected' : '' }}>Restore</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="{{ $tanggalMulai }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Tanggal Sampai</label>
                    <input type="date" name="tanggal_sampai" class="form-control" value="{{ $tanggalSampai }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <strong>Detail Log Aktivitas</strong>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="14%">Timestamp</th>
                            <th>User</th>
                            <th width="10%">Aksi</th>
                            <th width="15%">Entity</th>
                            <th width="8%">ID</th>
                            <th width="12%">IP Address</th>
                            <th>Perubahan Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $index => $log)
                        <tr>
                            <td>{{ $logs->firstItem() + $index }}</td>
                            <td>{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                            <td>
                                <strong>{{ $log->user->name ?? 'System' }}</strong>
                                <br><small class="text-muted">{{ $log->user->email ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->action === 'create' ? 'success' : ($log->action === 'update' ? 'warning' : ($log->action === 'delete' ? 'danger' : 'info')) }}">
                                    {{ strtoupper($log->action) }}
                                </span>
                            </td>
                            <td>{{ class_basename($log->entity_type) }}</td>
                            <td class="text-center">{{ $log->entity_id ?? '-' }}</td>
                            <td><code>{{ $log->ip_address ?? '-' }}</code></td>
                            <td>
                                @if($log->old_values && $log->new_values)
                                <small>
                                    @foreach($log->new_values as $key => $newValue)
                                        @php
                                            $oldValue = $log->old_values[$key] ?? null;
                                        @endphp
                                        @if($oldValue !== $newValue)
                                        <div class="text-danger small">
                                            <s>{{ $key }}: {{ is_null($oldValue) ? 'null' : Str::limit($oldValue, 30) }}</s>
                                        </div>
                                        <div class="text-success small fw-bold">
                                            → {{ is_null($newValue) ? 'null' : Str::limit($newValue, 30) }}
                                        </div>
                                        @endif
                                    @endforeach
                                </small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <p class="text-muted mt-2">Tidak ada log aktivitas untuk kriteria ini</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
            <div class="mt-3">
                {{ $logs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
