@extends('layout.layout')

@section('content')
    <div class="brands-page-wrapper py-5">
        <div class="container">
            {{-- Cabeçalho Minimalista --}}
            <div class="text-center mb-5">
                <h1 class="sax-title">{{ __('messages.nossas_marcas') }}</h1>
                <div class="sax-divider mx-auto"></div>
                <p class="text-muted small text-uppercase tracking-widest mt-3">
                    {{ __('messages.excelencia_detalhe') }}
                </p>
            </div>

            {{-- Busca Elegante --}}
            <div class="search-container mb-5">
                <form method="GET" class="mx-auto" style="max-width: 600px;">
                    <div class="sax-search-input">
                        <input type="text" name="search" 
                               placeholder="{{ __('messages.busca_marca') }}" 
                               value="{{ request('search') }}">
                        <button type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
            </div>

            {{-- Listagem em Grid de Luxo --}}
            <div class="row g-4">
                @forelse ($brands as $brand)
                    @php
                        $imagemInvalida = empty($brand->image) || 
                                          str_contains($brand->image, 'noimage') || 
                                          !Storage::disk('public')->exists($brand->image);
                    @endphp

                    @if (($brand->products_count ?? 0) > 0 && !$imagemInvalida)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ route('brands.show', $brand->slug) }}" class="brand-sax-card">
                                <div class="brand-img-box">
                                    <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name }}" loading="lazy">
                                </div>
                                <div class="brand-info">
                                    <h5 class="brand-name">{{ $brand->name ?? $brand->slug }}</h5>
                                </div>
                            </a>
                        </div>
                    @endif
                @empty
                    <div class="col-12 py-5 text-center">
                        <div class="no-results">
                            <i class="fas fa-search mb-3"></i>
                            <p>{{ __('messages.marcas_nao_encontradas') }}</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Paginação Customizada --}}
            <div class="sax-pagination mt-5">
                {{ $brands->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
