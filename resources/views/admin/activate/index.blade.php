@extends('layout.admin')

@section('content')
<x-admin.card>
<form action="{{ route('admin.activate.updateAll') }}" method="POST">
    @csrf
    <div class="sections-wrapper">
            
            {{-- Seção de Categorias --}}
            <div class="card-sax">
                <h2><i class="fa-solid fa-layer-group"></i> {{ __('messages.categorias') }}</h2>
                <div class="items-grid">
                    @foreach($categories as $category)
                    <div class="list-item">
                        <span title="{{ $category->name }}">{{ $category->name }}</span>
                        <input type="hidden" name="categories[{{ $category->id }}]" value="{{ $category->status }}" class="status-input">
                        <button type="button" class="status-badge {{ $category->status == 1 ? 'status-active' : 'status-inactive' }}" onclick="toggleUI(this)">
                            {{ $category->status == 1 ? __('messages.ativo') : __('messages.inativo') }}
                            <span>{{ $category->status == 1 ? '▴' : '▾' }}</span>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Seção de Marcas --}}
            <div class="card-sax">
                <h2><i class="fa-solid fa-tag"></i> {{ __('messages.marcas') }}</h2>
                <div class="items-grid">
                    @foreach($brands as $brand)
                    <div class="list-item">
                        <span title="{{ $brand->name }}">{{ $brand->name }}</span>
                        <input type="hidden" name="brands[{{ $brand->id }}]" value="{{ $brand->status }}" class="status-input">
                        <button type="button" class="status-badge {{ $brand->status == 1 ? 'status-active' : 'status-inactive' }}" onclick="toggleUI(this)">
                            {{ $brand->status == 1 ? __('messages.ativo') : __('messages.inativo') }}
                            <span>{{ $brand->status == 1 ? '▴' : '▾' }}</span>
                        </button>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

    <div class="footer-actions">
        <button type="submit" class="btn-save-all">
            <i class="fa-solid fa-cloud-arrow-up"></i> {{ __('messages.salvar_alteracoes') }}
        </button>
    </div>
</form>
</x-admin.card>
@endsection