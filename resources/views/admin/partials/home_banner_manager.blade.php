<div class="home-banner-manager"
    data-home-banner-manager
    data-group="{{ $managerGroup }}"
    data-upload-url="{{ route('admin.home-banners.store', $managerGroup) }}"
    data-reorder-url="{{ route('admin.home-banners.reorder', $managerGroup) }}">
    <div class="home-banner-manager__intro">
        <div>
            <span class="home-banner-manager__label"><i class="fa-solid fa-images"></i> Gestão em lote</span>
            <h3>{{ $managerTitle }}</h3>
            <p>{{ $managerDescription }}</p>
        </div>
        <div class="home-banner-manager__measure">
            <small>MEDIDA RECOMENDADA</small>
            <strong>{{ $managerDimensions }}</strong>
            <span>{{ $managerRatio }} · JPG, PNG ou WEBP · até 10 MB</span>
        </div>
    </div>

    <label class="home-banner-dropzone" data-banner-dropzone>
        <input type="file" multiple accept="image/jpeg,image/png,image/webp,image/avif" data-banner-input>
        <span class="home-banner-dropzone__icon"><i class="fa-solid fa-cloud-arrow-up"></i></span>
        <strong>Arraste várias imagens aqui</strong>
        <span>ou clique para selecionar no computador</span>
        <small data-upload-status>Nenhuma nova imagem selecionada</small>
    </label>

    <div class="home-banner-list {{ $managerItems->isEmpty() ? 'is-empty' : '' }}" data-banner-list>
        @foreach($managerItems as $banner)
            <article class="home-banner-item" draggable="true" data-banner-id="{{ $banner->id }}"
                data-update-url="{{ route('admin.home-banners.update', $banner) }}"
                data-images-url="{{ route('admin.home-banners.images', $banner) }}"
                data-delete-url="{{ route('admin.home-banners.destroy', $banner) }}">
                <div class="home-banner-item__media">
                    <picture>
                        @if($banner->mobile_image)<source media="(max-width: 767px)" srcset="{{ $banner->mobile_image_url }}">@endif
                        <img src="{{ $banner->image_url }}" alt="Banner {{ $loop->iteration }}" data-banner-preview>
                    </picture>
                    <span class="home-banner-item__order"><i class="fa-solid fa-grip-vertical"></i> <b>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</b></span>
                </div>
                <div class="home-banner-item__body">
                    <div class="home-banner-item__responsive-media">
                        <label class="home-banner-image-control">
                            <span><i class="fa-solid fa-desktop"></i> Desktop <small>imagem horizontal</small></span>
                            <input type="file" accept="image/jpeg,image/png,image/webp,image/avif" data-banner-image="desktop_image">
                            <strong>Alterar imagem</strong>
                        </label>
                        <label class="home-banner-image-control">
                            <span><i class="fa-solid fa-mobile-screen"></i> Mobile <small>1080 × 1350 px</small></span>
                            <img src="{{ $banner->mobile_image_url }}" alt="Prévia mobile" data-banner-mobile-preview>
                            <input type="file" accept="image/jpeg,image/png,image/webp,image/avif" data-banner-image="mobile_image">
                            <strong>{{ $banner->mobile_image ? 'Alterar imagem' : 'Adicionar imagem' }}</strong>
                        </label>
                        <button type="button" class="home-banner-mobile-remove {{ $banner->mobile_image ? '' : 'd-none' }}" data-banner-mobile-remove>
                            <i class="fa-solid fa-rotate-left"></i> Usar desktop no mobile
                        </button>
                    </div>
                    <label>Link deste banner</label>
                    <div class="home-banner-item__link">
                        <i class="fa-solid fa-link"></i>
                        <input type="text" value="{{ $banner->link }}" placeholder="https://... ou /categorias/..." data-banner-link>
                        <span class="home-banner-save-state" data-save-state></span>
                    </div>
                    @if($managerGroup === \App\Models\HomeBanner::GROUP_MAIN)
                        <details class="home-banner-copy">
                            <summary><span><i class="fa-solid fa-language"></i> Título e descrição deste slide</span><i class="fa-solid fa-chevron-down"></i></summary>
                            <div class="home-banner-copy__languages">
                                @foreach(['pt' => 'Português', 'en' => 'English', 'es' => 'Español'] as $language => $languageLabel)
                                    <fieldset>
                                        <legend>{{ $languageLabel }}</legend>
                                        <label for="banner-{{ $banner->id }}-title-{{ $language }}">Título</label>
                                        <input id="banner-{{ $banner->id }}-title-{{ $language }}" type="text" maxlength="160"
                                               value="{{ $banner->{'title_'.$language} }}" data-banner-copy-field="title_{{ $language }}">
                                        <label for="banner-{{ $banner->id }}-description-{{ $language }}">Descrição</label>
                                        <textarea id="banner-{{ $banner->id }}-description-{{ $language }}" rows="3" maxlength="600"
                                                  data-banner-copy-field="description_{{ $language }}">{{ $banner->{'description_'.$language} }}</textarea>
                                    </fieldset>
                                @endforeach
                            </div>
                        </details>
                    @endif
                    <div class="home-banner-item__actions">
                        <label class="home-banner-toggle">
                            <input type="checkbox" data-banner-active @checked($banner->is_active)>
                            <span></span> Exibir no site
                        </label>
                        <button type="button" data-banner-delete><i class="fa-solid fa-trash"></i> Remover</button>
                    </div>
                </div>
            </article>
        @endforeach
        <div class="home-banner-empty" data-banner-empty>
            <i class="fa-regular fa-images"></i>
            <strong>Nenhuma imagem neste conjunto</strong>
            <span>Selecione uma ou várias imagens acima para começar.</span>
        </div>
    </div>
</div>
