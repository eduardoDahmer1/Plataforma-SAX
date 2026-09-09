<section class="sax-exclusive-section py-5">
    <div class="container-fluid px-lg-5">
        <div class="exclusive-shell row g-4 align-items-stretch">
            <div class="col-lg-5">
                <div class="exclusive-panel h-100">
                    @if(filled($sectionContent['title']) || filled($sectionContent['description']))
                        <span class="exclusive-eyebrow">{{ __('messages.curadoria_sax') }}</span>
                    @endif
                    @if(filled($sectionContent['title']))
                        <h2 class="exclusive-title">{{ $sectionContent['title'] }}</h2>
                    @endif
                    @if(filled($sectionContent['description']))
                        <p class="exclusive-copy">{{ $sectionContent['description'] }}</p>
                    @endif

                    @if ($exclusiveCategories->isNotEmpty())
                        <div class="exclusive-tags">
                            @foreach ($exclusiveCategories as $category)
                                <a href="{{ route('categories.show', $category->slug ?? $category->id) }}" class="exclusive-tag">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    @endif

                    <div class="exclusive-actions">
                        <a href="{{ route('categories.index') }}" class="exclusive-btn exclusive-btn--dark">{{ __('messages.explorar_colecao') }}</a>
                        <a href="{{ route('blogs.index') }}" class="exclusive-btn exclusive-btn--ghost">{{ __('messages.ver_editorial') }}</a>
                    </div>

                    <div class="exclusive-note">
                        <strong>{{ __('messages.selecao_com_intencao') }}</strong>
                        {{ __('messages.exclusive_note_text') }}
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="exclusive-media-wrap h-100">
                    @if (isset($banner9) && $banner9)
                        @if (!empty($banner9_link))
                            <a href="{{ $banner9_link }}" aria-label="{{ __('messages.abrir_banner_principal') }}">
                                <img src="{{ asset('storage/uploads/' . $banner9) }}" class="img-fluid w-100 exclusive-media"
                                     alt="{{ __('messages.colecao_exclusiva_sax') }}" loading="lazy" decoding="async" onerror="this.style.display='none'">
                            </a>
                        @else
                            <img src="{{ asset('storage/uploads/' . $banner9) }}" class="img-fluid w-100 exclusive-media"
                                 alt="{{ __('messages.colecao_exclusiva_sax') }}" loading="lazy" decoding="async" onerror="this.style.display='none'">
                        @endif
                    @else
                        <div class="exclusive-media exclusive-media--placeholder">
                            <div><span class="exclusive-eyebrow">{{ __('messages.colecao_exclusiva') }}</span><p class="mb-0">{{ __('messages.adicione_banner_destaque') }}</p></div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
