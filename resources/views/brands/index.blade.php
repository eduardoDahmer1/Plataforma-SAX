@extends('layout.layout')

@section('content')
    <x-directory-hero eyebrow="SAX Selection"
        :title="__('messages.nossas_marcas')" :description="__('messages.excelencia_detalhe')" />

    <div class="brands-page-wrapper brands-page-wrapper--editorial py-4 py-lg-5">
        <div class="container">
            <div class="catalog-directory-tools mb-4 mb-lg-5">
                <x-directory-search :action="route('brands.index')" :placeholder="__('messages.busca_marca')"
                    :value="request('search', '')" :clear-url="route('brands.index')" />
            </div>

            <div class="sax-directory-grid">
                @forelse ($brands as $brand)
                    @php
                        $brandImage = !empty($brand->image)
                            ? Storage::url($brand->image)
                            : asset('storage/uploads/noimage.webp');
                        $fallbackImage = asset('storage/uploads/noimage.webp');
                    @endphp

                    @if (($brand->active_products_count ?? 0) > 0)
                        <a href="{{ route('brands.show', $brand->slug) }}" class="sax-directory-card">
                            <div class="sax-directory-card__media sax-directory-card__media--logo">
                                <img src="{{ $brandImage }}" alt="{{ $brand->name }}" loading="lazy"
                                    onerror="this.src='{{ $fallbackImage }}'">
                            </div>
                            <div class="sax-directory-card__body">
                                <div class="sax-directory-card__copy">
                                    <span>{{ __('messages.marca') }}</span>
                                    <h2>{{ $brand->name ?? $brand->slug }}</h2>
                                    <small>{{ trans_choice('messages.produtos_disponiveis', $brand->active_products_count, ['count' => $brand->active_products_count]) }}</small>
                                </div>
                                <span class="sax-directory-card__arrow">
                                    <i class="fas fa-arrow-right" aria-hidden="true"></i>
                                </span>
                            </div>
                        </a>
                    @endif
                @empty
                    <div class="catalog-directory-empty">
                        <div class="no-results">
                            <i class="fas fa-search mb-3"></i>
                            <p>{{ __('messages.marcas_nao_encontradas') }}</p>
                        </div>
                    </div>
                @endforelse
            </div>

            <div class="sax-pagination mt-5">
                {{ $brands->appends(request()->input())->links() }}
            </div>
        </div>
    </div>
@endsection
