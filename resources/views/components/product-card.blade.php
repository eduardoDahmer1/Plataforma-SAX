@props([
    'item',
    'cartItems' => [],
    'gridClass' => 'col-6 col-md-6 col-lg-2 mb-1 g-1',
    'displayName' => null,
])

@php
    $isOutOfStock = ($item->stock ?? 0) <= 0;
    if (!filled($displayName)) {
        $productLocale = translation_locale();
        $commercialName = filled($item->name) ? $item->name : ($item->external_name ?? '');
        $translation = null;

        if ($productLocale !== 'pt-br') {
            $translation = $item->relationLoaded('translations')
                ? $item->translations->firstWhere('locale', $productLocale)
                : null;

            // Coleções antigas em cache podem conter somente outro idioma.
            if (!$translation) {
                $translation = $item->translations()
                    ->where('locale', $productLocale)
                    ->first();
            }
        }

        $translatedName = trim((string) ($translation?->name ?? ''));
        $externalName = trim((string) ($item->external_name ?? ''));
        $isLegacyExternalName = $translatedName !== ''
            && $externalName !== ''
            && mb_strtolower($translatedName) === mb_strtolower($externalName)
            && mb_strtolower(trim((string) $commercialName)) !== mb_strtolower($externalName);

        $displayName = $translatedName !== '' && !$isLegacyExternalName
            ? $translatedName
            : $commercialName;
    }

    $fotoExibir = 'https://placehold.co/400x533/f5f5f5/999?text=No+Image';
    if (!empty($item->photo_url)) {
        $fotoExibir = $item->photo_url;
    } elseif (!empty($item->photo)) {
        $fotoExibir = asset('storage/uploads/products/' . $item->photo);
    }

    $providedColorVariants = collect($item->card_color_variants ?? []);
    $colorVariants = ($providedColorVariants->isNotEmpty()
            ? $providedColorVariants
            : collect($item->resolved_card_color_variants ?? []))
        ->filter(fn ($variant) => is_array($variant) && !empty($variant['color']) && (!empty($variant['slug']) || !empty($variant['id'])))
        ->unique(fn ($variant) => implode(',', $variant['colors'] ?? [$variant['color']]))
        ->take(6)
        ->values();

    $productSize = trim((string) ($item->size ?? ''));
    $hoverPhotoUrl = $item->card_hover_photo_url;
@endphp

<div class="{{ $gridClass }}">
    <div class="card h-100 border-0 rounded-0 product-card-standard {{ $hoverPhotoUrl ? 'has-hover-image' : '' }} {{ $isOutOfStock ? 'sax-out-of-stock' : '' }}">

        <div class="product-card-standard__media jw-img-container position-relative">
            <a href="{{ route('produto.show', $item->slug ?? $item->id) }}"
               class="product-card-standard__media-link"
               aria-label="{{ $displayName }}">
                <img src="{{ $fotoExibir }}"
                     class="card-img-top img-fluid rounded-0 product-card-standard__image product-card-standard__image--primary"
                     alt="{{ $displayName }}">
                @if($hoverPhotoUrl)
                    <img src="{{ $hoverPhotoUrl }}"
                         class="card-img-top img-fluid rounded-0 product-card-standard__image product-card-standard__image--secondary"
                         alt=""
                         loading="lazy"
                         aria-hidden="true">
                @endif
            </a>

            <x-cupon-selo :product="$item" variante="card" />

            <div class="position-absolute top-0 end-0 p-3">
                @if (Auth::check() && Auth::user()->user_type != 1)
                    <x-product-favorite-button :item="$item" />
                @endif
            </div>
        </div>

        <div class="card-body product-card-standard__body d-flex flex-column">
            <a href="{{ route('produto.show', $item->slug ?? $item->id) }}" class="text-decoration-none text-dark">
                <div class="product-card-standard__brand">
                    {{ $item->brand->name ?? __('messages.brand_name') }}
                </div>

                <div class="product-card-standard__name" title="{{ $displayName }}">
                    {{ $displayName }}
                </div>

                <div class="product-card-standard__meta mt-auto">
                    <div class="product-card-standard__price">
                        {{ isset($item->price) ? currency_format($item->price, 2, ',', '.') : '0,00' }}
                    </div>
                    <div class="product-card-standard__sku">
                        {{ __('messages.sku_prefix') }} {{ $item->sku ?? __('messages.not_available_short') }}
                    </div>
                </div>
            </a>

            <div class="product-card-standard__variants">
                @if ($colorVariants->isNotEmpty())
                    <div class="product-card-standard__colors" aria-label="{{ __('messages.cores_disponiveis') }}">
                        @foreach ($colorVariants as $variant)
                            @php
                                $hex = $variant['color'];
                                $variantColors = $variant['colors'] ?? [$hex];
                                $swatchStyle = $variant['swatch_style'] ?? ('background-color: ' . $hex . ';');
                                $isCurrentColor = (int) ($variant['id'] ?? 0) === (int) $item->id
                                    || implode(',', $item->product_colors) === implode(',', $variantColors);
                            @endphp
                            <a href="{{ route('produto.show', $variant['slug'] ?? $variant['id']) }}"
                               class="product-card-standard__color-link {{ $isCurrentColor ? 'is-current' : '' }}"
                               aria-label="{{ __('messages.view_product_color', ['color' => implode(' + ', $variantColors)]) }}"
                               title="{{ implode(' + ', $variantColors) }}">
                                <span class="product-card-standard__color-dot" style="{{ $swatchStyle }}"></span>
                            </a>
                        @endforeach
                    </div>
                @endif

                @if ($productSize !== '')
                    <span class="product-card-standard__size">{{ __('messages.tam_prefix') }} {{ $productSize }}</span>
                @endif
            </div>
        </div>
    </div>
</div>
