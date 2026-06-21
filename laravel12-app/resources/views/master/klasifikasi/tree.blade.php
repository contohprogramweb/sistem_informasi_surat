@php
    $indent = $level * 20;
@endphp

<ul class="list-unstyled">
    @foreach($items as $item)
        <li>
            <div class="d-flex align-items-center py-2 border-bottom" style="padding-left: {{ $indent }}px;">
                <i class="bi bi-folder me-2 text-warning"></i>
                <span class="fw-bold">{{ $item->kode }}</span>
                <span class="ms-2">{{ $item->nama }}</span>
                @if($item->deleted_at)
                    <span class="badge bg-danger ms-2">Terhapus</span>
                @endif
                <div class="ms-auto">
                    <button class="btn btn-sm btn-info text-white btn-edit" data-id="{{ $item->id }}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    @if(!$item->deleted_at)
                        <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $item->id }}">
                            <i class="bi bi-trash"></i>
                        </button>
                    @else
                        <button class="btn btn-sm btn-success btn-restore" data-id="{{ $item->id }}">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </button>
                    @endif
                </div>
            </div>
            
            @if(count($item->children) > 0)
                @include('master.klasifikasi.tree', ['items' => $item->children, 'level' => $level + 1])
            @endif
        </li>
    @endforeach
</ul>
