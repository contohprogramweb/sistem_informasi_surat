@foreach($nodes as $node)
<div class="timeline-node timeline-level-{{ $level }}">
    <div class="timeline-icon bg-{{ $node['status_color'] }}">
        @if($level === 0)
            <i class="bi bi-person-badge"></i>
        @else
            <i class="bi bi-arrow-return-right"></i>
        @endif
    </div>
    
    <div class="timeline-content">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <strong>{{ $node['dari'] }}</strong> 
                <i class="bi bi-arrow-right text-muted"></i> 
                <strong>{{ $node['ke'] }}</strong>
                @if($node['is_overdue'])
                    <span class="badge bg-danger ms-2">Terlambat</span>
                @endif
            </div>
            <small class="text-muted">{{ $node['created_at'] }}</small>
        </div>
        
        <div class="mb-2">
            <span class="badge bg-{{ $node['status_color'] }}">{{ $node['status'] }}</span>
            <span class="badge bg-secondary">{{ $node['prioritas'] }}</span>
            @if($node['batas_waktu'])
                <span class="badge bg-light text-dark"><i class="bi bi-calendar"></i> {{ $node['batas_waktu'] }}</span>
            @endif
        </div>
        
        <div class="mb-2">
            <small class="text-muted fw-bold">Instruksi:</small>
            <p class="mb-1">{!! nl2br(e($node['instruksi'])) !!}</p>
        </div>
        
        @if($node['read_at'])
        <div class="mb-2">
            <small class="text-success"><i class="bi bi-check-circle"></i> Dibaca {{ $node['read_at'] }}</small>
        </div>
        @endif
        
        @if($node['komentar_selesai'])
        <div class="mb-2">
            <small class="text-muted fw-bold">Komentar:</small>
            <p class="mb-1 text-primary">{!! nl2br(e($node['komentar_selesai'])) !!}</p>
        </div>
        @endif
        
        @if($node['file_tindak_lanjut'])
        <div class="mb-2">
            <a href="{{ Storage::url($node['file_tindak_lanjut']) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-paperclip"></i> Lihat Lampiran
            </a>
        </div>
        @endif
    </div>
    
    @if(!empty($node['children']))
        @include('disposisi._timeline_nodes', ['nodes' => $node['children'], 'level' => $level + 1])
    @endif
</div>
@endforeach
