@props(['stock'])

@if ($stock > 0)
    <span class="badge bg-success"><i class="fas fa-box me-1"></i> {{ $stock }} em estoque</span>
@else
    <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Sem estoque</span>
@endif
