@extends('layout.admin')

@section('content')
<style>
    /* Botões mais compactos */
    .status-badge {
        padding: 4px 10px;
        border-radius: 4px;
        color: white;
        font-weight: 600;
        font-size: 0.75rem; /* Fonte menor */
        display: inline-flex;
        align-items: center;
        border: none;
        cursor: pointer;
        transition: 0.2s;
        min-width: 80px;
        justify-content: center;
    }
    .status-active { background-color: #198754; } 
    .status-inactive { background-color: #212529; } 
    .status-badge:hover { opacity: 0.85; }

    /* Container Principal */
    .sections-wrapper {
        display: flex;
        flex-direction: column;
        gap: 25px;
        padding: 15px;
    }

    .card-sax {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        padding: 15px;
    }

    .card-sax h2 { 
        font-size: 1.1rem; /* Título menor */
        color: #333;
        border-bottom: 2px solid #f8d7da;
        padding-bottom: 8px;
        margin-bottom: 15px;
    }

    /* Grid de 4 colunas responsiva */
    .items-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr); /* Força 4 colunas */
        gap: 15px;
    }

    /* Item individual */
    .list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px;
        background: #fcfcfc;
        border: 1px solid #eee;
        border-radius: 5px;
    }

    .list-item span {
        font-size: 0.85rem; /* Fonte do nome menor */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-right: 5px;
    }

    /* Ajuste para telas menores */
    @media (max-width: 1200px) { .items-grid { grid-template-columns: repeat(3, 1fr); } }
    @media (max-width: 992px) { .items-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 576px) { .items-grid { grid-template-columns: 1fr; } }
</style>

<div class="container-fluid">
    <div class="sections-wrapper">
        
        <div class="card-sax">
            <h2><i class="fa-solid fa-layer-group"></i> Categorias</h2>
            <div class="items-grid">
                @foreach($categories as $category)
                <div class="list-item">
                    <span title="{{ $category->name }}"><strong>{{ $category->name }}</strong></span>
                    <form action="{{ route('admin.activate.toggle', ['category', $category->id]) }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="status-badge {{ $category->status == 1 ? 'status-active' : 'status-inactive' }}">
                            {{ $category->status == 1 ? 'Ativo' : 'Inativo' }}
                            <span style="margin-left: 5px;">{{ $category->status == 1 ? '▴' : '▾' }}</span>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card-sax">
            <h2><i class="fa-solid fa-tag"></i> Marcas</h2>
            <div class="items-grid">
                @foreach($brands as $brand)
                <div class="list-item">
                    <span title="{{ $brand->name }}"><strong>{{ $brand->name }}</strong></span>
                    <form action="{{ route('admin.activate.toggle', ['brand', $brand->id]) }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="status-badge {{ $brand->status == 1 ? 'status-active' : 'status-inactive' }}">
                            {{ $brand->status == 1 ? 'Ativo' : 'Inativo' }}
                            <span style="margin-left: 5px;">{{ $brand->status == 1 ? '▴' : '▾' }}</span>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
@endsection