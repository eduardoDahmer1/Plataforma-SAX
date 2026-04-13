@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="{{ __('messages.categorias_filhas_titulo') }}"
        description="{!! __('messages.gerenciando_terminais_desc', ['total' => $categoriasfilhas->total()]) !!}">
        <x-slot:actions>
            <a href="{{ route('admin.categorias-filhas.create') }}" class="btn btn-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                <i class="fa fa-sitemap me-2"></i> {{ __('messages.nova_sub_subcategoria') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- Busca Refinada --}}
    <div class="sax-search-wrapper mb-4">
        <form action="{{ route('admin.categorias-filhas.index') }}" method="GET">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0 px-3">
                    <i class="fa fa-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-0 sax-search-input py-2"
                    placeholder="{{ __('messages.buscar_terminal_placeholder') }}" value="{{ request('search') }}">
                <button class="btn btn-dark rounded-3 px-4 m-1" type="submit">{{ __('messages.buscar_btn') }}</button>
            </div>
        </form>
    </div>

    @if (session('success'))
        <div class="alert alert-sax-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Grid de Child Categories --}}
    <div class="row g-3">
        @foreach ($categoriasfilhas as $filha)
            <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                <div class="sax-child-card h-100 shadow-sm border-0 d-flex flex-column p-4">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <a href="{{ route('categorias-filhas.show', $filha->slug) }}" target="_blank"
                                class="text-muted hover-dark">
                                <i class="fa fa-external-link-alt x-small"></i>
                            </a>
                        </div>
                        <h5 class="child-title text-truncate" title="{{ $filha->name }}">
                            {{ $filha->name }}
                        </h5>
                        <div class="parent-info mt-2">
                            <label class="sax-label-tiny mb-0">{{ __('messages.subcategoria_pai_label') }}</label>
                            <p class="text-muted small text-truncate m-0">
                                <i class="fa fa-level-up-alt fa-rotate-90 me-1"></i>
                                {{ $filha->subcategory ? ($filha->subcategory->name ?: $filha->subcategory->slug) : __('messages.sin_vinculo') }}
                            </p>
                        </div>
                    </div>

                    {{-- Ações Compactas --}}
                    <div class="sax-action-grid mt-auto pt-3 border-top">
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="{{ route('admin.categorias-filhas.show', $filha->id) }}"
                                    class="btn btn-action-sax w-100" title="{{ __('messages.ver_admin') }}">
                                    <i class="fa fa-eye"></i>
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="{{ route('admin.categorias-filhas.edit', $filha->id) }}"
                                    class="btn btn-action-sax w-100" title="{{ __('messages.editar') }}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </div>
                            <div class="col-12">
                                <form action="{{ route('admin.categorias-filhas.destroy', $filha->id) }}"
                                    method="POST" onsubmit="return confirm('{{ __('messages.confirmar_eliminar_filha') }}')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-delete-sax w-100">
                                        <i class="fa fa-trash-alt me-2"></i>{{ __('messages.eliminar_btn') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-5">
        {{ $categoriasfilhas->links() }}
    </div>
</x-admin.card>
@endsection