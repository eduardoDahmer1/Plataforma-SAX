<footer class="sax-footer-refined">
    <div class="container">
        @php
            $storeControls = app(\App\Services\StoreControlService::class);
            $isOtica = $storeControls->isOtica();
            $footerCategories = $mainCategories ?? collect();

            $labelMap = [
                'feminino'  => __('messages.mulher'),
                'masculino' => __('messages.homem'),
                'infantil'  => __('messages.criancas'),
                'optico'    => __('messages.lente'),
                'casa'      => __('messages.casa'),
            ];
            if ($isOtica) {
                $opticalTerms = ['optico', 'otica', 'oculos', 'lente', 'eyewear', 'optical'];
                $footerCategories = $footerCategories->filter(function ($category) use ($opticalTerms) {
                    $text = strtolower(($category->slug ?? '') . ' ' . ($category->name ?? ''));
                    return collect($opticalTerms)->contains(fn ($term) => str_contains($text, $term));
                });
            }
        @endphp

        <div class="footer-mobile">
            <div class="footer-mobile-brand">
                <a href="{{ route('home') }}" aria-label="SAX">SAX</a>
                <span>Style · Arts · Xtras</span>
            </div>

            <div class="footer-mobile-social">
                <a href="https://www.instagram.com/saxdepartment/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="https://www.facebook.com/saxdepartmentstore" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://www.tiktok.com/@saxdepartment" target="_blank" rel="noopener noreferrer" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            </div>

            <div class="footer-mobile-accordions">
                @if ($storeControls->navigationVisible('footer', 'categories'))<details open>
                    <summary>{{ __('messages.categorias') }} <i class="fa-solid fa-chevron-down"></i></summary>
                    <ul>
                        @foreach ($footerCategories as $cat)
                            <li><a href="{{ url('categorias/' . $cat->slug) }}">{{ $labelMap[$cat->slug] ?? strtoupper($cat->name) }}</a></li>
                        @endforeach
                        @unless ($isOtica)
                            <li><a href="{{ route('categories.index') }}">{{ __('messages.todas_categorias') }}</a></li>
                            <li><a href="{{ route('brands.index') }}">{{ __('messages.nossas_marcas') }}</a></li>
                        @endunless
                    </ul>
                </details>@endif

                @if ($storeControls->navigationVisible('footer', 'institucional') || $storeControls->navigationVisible('footer', 'bridal') || $storeControls->navigationVisible('footer', 'palace') || $storeControls->navigationVisible('footer', 'cafe') || $storeControls->navigationVisible('footer', 'blog'))<details>
                    <summary>{{ __('messages.sobre_nos') }} <i class="fa-solid fa-chevron-down"></i></summary>
                    <ul>
                        @if ($storeControls->navigationVisible('footer', 'institucional'))<li><a href="{{ route('institucional.index') }}">{{ __('messages.institucional') }}</a></li>@endif
                        @if ($storeControls->navigationVisible('footer', 'bridal'))<li><a href="{{ route('bridal.index') }}">{{ __('messages.bridal') }}</a></li>@endif
                        @if ($storeControls->navigationVisible('footer', 'palace'))<li><a href="{{ route('palace.index') }}">{{ __('messages.palace') }}</a></li>@endif
                        @if ($storeControls->navigationVisible('footer', 'cafe'))<li><a href="{{ route('cafe_bistro.index') }}">{{ __('messages.cafe_bistro') }}</a></li>@endif
                        @if ($storeControls->navigationVisible('footer', 'blog'))<li><a href="{{ route('blogs.index') }}">#SAXNEWS</a></li>@endif
                    </ul>
                </details>@endif

                @if ($storeControls->navigationVisible('footer', 'contact'))<details>
                    <summary>{{ __('messages.atendimento') }} <i class="fa-solid fa-chevron-down"></i></summary>
                    <ul>
                        <li><a href="{{ route('contact.form') }}">{{ __('messages.ajuda') }}</a></li>
                        <li><a href="{{ route('policies.index') }}">Políticas e Termos</a></li>
                        <li><a href="{{ route('contact.form') }}">{{ __('messages.contato') }}</a></li>
                    </ul>
                </details>@endif
            </div>
        </div>

        <div class="footer-grid">
            @if ($storeControls->navigationVisible('footer', 'categories'))<div class="footer-column">
                <h6 class="footer-title">{{ __('messages.categorias') }}</h6>
                <ul class="footer-links">
                    @foreach ($footerCategories as $cat)
                        <li>
                            <a href="{{ url('categorias/' . $cat->slug) }}">
                                {{ $labelMap[$cat->slug] ?? strtoupper($cat->name) }}
                            </a>
                        </li>
                    @endforeach
                    @if (!$isOtica && $storeControls->navigationVisible('footer', 'bridal'))<li><a href="{{ route('bridal.index') }}">{{ __('messages.bridal') }}</a></li>@endif
                    @if (!$isOtica && $storeControls->navigationVisible('footer', 'palace'))<li><a href="{{ route('palace.index') }}">{{ __('messages.palace') }}</a></li>@endif
                    @unless ($isOtica)<li><a href="{{ route('categories.index') }}">{{ __('messages.todas_categorias') }}</a></li>@endunless
                </ul>
            </div>@endif

            <div class="footer-column border-left">
                <div class="column-content">
                    <h6 class="footer-title">{{ __('messages.sobre_nos') }}</h6>
                    <ul class="footer-links">
                        @unless ($isOtica)
                            <li><a href="{{ route('all-categories.index') }}">{{ __('messages.categorias_gerais') }}</a></li>
                            <li><a href="{{ route('brands.index') }}">{{ __('messages.nossas_marcas') }}</a></li>
                        @endunless
                        @if ($storeControls->navigationVisible('footer', 'blog'))<li><a href="{{ route('blogs.index') }}">#SAXNEWS</a></li>@endif
                        @if ($storeControls->navigationVisible('footer', 'palace'))<li><a href="{{ route('palace.index') }}">SAX Palace</a></li>@endif
                        @if ($storeControls->navigationVisible('footer', 'contact'))<li><a href="{{ route('contact.form') }}">{{ __('messages.trabalhe_conosco') }}</a></li>@endif
                        @unless ($isOtica)<li><a href="https://saxdepartment.com/categorias-filhas/edition-privee">édition privée</a></li>@endunless
                    </ul>
                </div>
            </div>

            <div class="footer-column border-left">
                <div class="column-content">
                    <h6 class="footer-title">{{ __('messages.atendimento') }}</h6>
                    <ul class="footer-links">
                        <li><a href="{{ route('contact.form') }}">{{ __('messages.ajuda') }}</a></li>
                        <li><a href="{{ route('policies.index') }}">Políticas e Termos</a></li>
                    </ul>

                    <h6 class="footer-title social-title">{{ __('messages.siga_redes') }}</h6>
                    <div class="footer-social-icons">
                        <a href="https://www.instagram.com/saxdepartment/" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.facebook.com/saxdepartmentstore" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://www.tiktok.com/@saxdepartment" target="_blank" rel="noopener noreferrer"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-copyright">
            <p>{{ date('Y') }}. {{ __('messages.direitos') }}. SAX E-commerce.</p>
        </div>
    </div>
</footer>
