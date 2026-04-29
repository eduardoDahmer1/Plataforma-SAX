@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.subcategorias_titulo') }}"
        description="{{ __('messages.estrutura_de') }} <span class='text-dark fw-bold'>{{ $subcategories->total() }}</span> {{ __('messages.niveis_secundarios') }}">
        <x-slot:actions>
            <a href="{{ route('admin.subcategories.create') }}" class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                <i class="fa fa-folder-plus me-2"></i> {{ __('messages.nova_subcategoria') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Filtro de Pesquisa --}}
    <div class="sax-search-wrapper mb-4">
        <form action="{{ route('admin.subcategories.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0 px-3">
                    <i class="fa fa-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-0 sax-search-input py-2"
                    placeholder="{{ __('messages.buscar_subcategoria_placeholder') }}" value="{{ request('search') }}">
                <button class="btn btn-dark rounded-3 px-4 m-1" type="submit">{{ __('messages.buscar_botao') }}</button>
            </div>
        </form>
    </div>

    <x-admin.alert />

    {{-- Grid de Subcategorias --}}
    <div class="row g-3">
        @foreach($subcategories as $subcategory)
        <div class="col-12 col-sm-6 col-lg-4">
            <div class="sax-subcategory-card h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="subcategory-tag">Subnivel #{{ $subcategory->id }}</span>
                        <a href="{{ route('subcategories.show', $subcategory->slug) }}" target="_blank" class="text-muted hover-dark transition-all">
                            <i class="fa fa-external-link-alt small"></i>
                        </a>
                    </div>

                    <h5 class="subcategory-title mb-1">{{ $subcategory->name }}</h5>

                    {{-- Badge da Categoria Pai --}}
                    <div class="parent-category-badge mb-4">
                        <i class="fa fa-level-up-alt fa-rotate-90 me-1 opacity-50"></i>
                        <strong class="text-dark">
                            @if($subcategory->category)
                                {{ $subcategory->category->name ?: $subcategory->category->slug }}
                            @else
                                <span class="text-danger">{{ __('messages.sem_categoria') }}</span>
                            @endif
                        </strong>
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="sax-action-group mt-auto pt-3 border-top">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('admin.subcategories.show', $subcategory) }}" class="btn btn-action-sax w-100">
                                    <i class="fa fa-file-alt"></i> {{ __('messages.dados') }}
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.subcategories.edit', $subcategory) }}" class="btn btn-action-sax w-100">
                                    <i class="fa fa-pen"></i> {{ __('messages.editar') }}
                                </a>
                            </div>
                            <div class="col-12">
                                <form action="{{ route('admin.subcategories.destroy', $subcategory) }}" method="POST" onsubmit="return confirm('{{ __('messages.confirmar_eliminar_subcategoria') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-delete-sax w-100 mt-1">
                                        <i class="fa fa-trash-alt me-1"></i> {{ __('messages.eliminar_registro') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-5">
        {{ $subcategories->links() }}
    </div>
</x-admin.card>
@endsection