@extends('layout.admin')

@section('content')
@push('styles')
    <link href="{{ asset('css/theme-admin.css') }}?v={{ filemtime(public_path('css/theme-admin.css')) }}" rel="stylesheet">
@endpush

@php
    $previewUrls = [
        'storefront' => url('/'),
        'blog' => url('/blogs'),
        'checkout' => url('/cart'),
        'institutional' => url('/institucional'),
        'bridal' => url('/bridal'),
        'palace' => url('/palace'),
        'bistro_pjc' => url('/bistro'),
        'bistro_asuncion' => url('/bistro/asuncion'),
    ];
    $colorFields = [
        'primary_color' => ['Cor principal', 'Base da identidade e áreas de destaque.'],
        'accent_color' => ['Cor de destaque', 'Detalhes, ícones, linhas e chamadas.'],
        'background_color' => ['Fundo principal', 'Cor base da página.'],
        'surface_color' => ['Superfícies', 'Cards e seções secundárias.'],
        'heading_color' => ['Títulos', 'Títulos sobre fundos claros.'],
        'subtitle_color' => ['Subtítulos', 'Etiquetas, legendas e textos auxiliares.'],
        'body_color' => ['Descrições', 'Parágrafos e textos corridos.'],
        'inverse_text_color' => ['Texto em fundo escuro', 'Títulos e textos sobre fotos ou áreas escuras.'],
        'link_color' => ['Links', 'Links textuais e ações discretas.'],
        'button_background' => ['Botões principais', 'Fundo das principais chamadas para ação.'],
        'button_text' => ['Texto dos botões', 'Texto e ícones dos botões principais.'],
    ];
    $headerFooterFields = [
        'header_background_color' => ['Fundo do header', 'Fundo da navegação, inclusive ao rolar.'],
        'header_text_color' => ['Texto do header', 'Menus, links e controles do cabeçalho.'],
        'header_accent_color' => ['Destaque do header', 'Hover, ícones e indicação ativa.'],
        'footer_background_color' => ['Fundo do footer', 'Fundo principal do rodapé.'],
        'footer_heading_color' => ['Títulos do footer', 'Títulos das colunas e marca.'],
        'footer_text_color' => ['Textos do footer', 'Links, endereço, horários e copyright.'],
    ];
    $interfaceColorFields = [
        'border_color' => ['Bordas e divisórias', 'Cards, campos e separadores.'],
        'hover_color' => ['Interações e hover', 'Links e ações ao passar o mouse.'],
    ];
    $initialTheme = reset($themes);
@endphp

<div class="theme-admin" id="themeAdmin"
     data-update-url="{{ route('admin.theme-settings.update', ['scope' => '__scope__'], false) }}"
     data-reset-url="{{ route('admin.theme-settings.reset', ['scope' => '__scope__'], false) }}">
    <script type="application/json" id="themeAdminData">@json($themes)</script>
    <script type="application/json" id="themeFontData">@json($fontOptions)</script>

    <header class="theme-admin__hero">
        <div>
            <span>Design system</span>
            <h1>Identidade visual</h1>
            <p>Edite cores, fontes, header, footer e componentes por área. Sem personalização, todo o visual original é preservado.</p>
        </div>
        <div class="theme-admin__hero-actions">
            <span class="theme-save-state" id="themeSaveState" role="status" aria-live="polite">
                <i class="fa-solid fa-circle-check"></i><span>Pronto</span>
            </span>
            <a href="{{ $previewUrls['storefront'] }}" target="_blank" class="theme-preview-link" id="themePreviewLink">
                Ver página <i class="fa-solid fa-arrow-up-right-from-square"></i>
            </a>
        </div>
    </header>

    <div class="theme-admin__layout">
        <aside class="theme-scope-list" aria-label="Áreas do site">
            <div class="theme-scope-list__heading">
                <span>Áreas do site</span>
                <small>Configurações isoladas</small>
            </div>
            @foreach($themes as $scope => $theme)
                <button type="button" class="theme-scope {{ $loop->first ? 'is-active' : '' }}"
                        data-theme-scope="{{ $scope }}" data-preview-url="{{ $previewUrls[$scope] }}">
                    <span class="theme-scope__icon"><i class="fa-solid {{ $theme['meta']['icon'] }}"></i></span>
                    <span class="theme-scope__copy">
                        <strong>{{ $theme['meta']['label'] }}</strong>
                        <small>{{ $theme['meta']['description'] }}</small>
                    </span>
                    <span class="theme-scope__status {{ $theme['customized'] ? 'is-custom' : '' }}" data-scope-status>
                        {{ $theme['customized'] ? 'Personalizado' : 'Original' }}
                    </span>
                </button>
            @endforeach
        </aside>

        <main class="theme-editor">
            <div class="theme-editor__heading">
                <div>
                    <span id="themeEditorKicker">Área selecionada</span>
                    <h2 id="themeEditorTitle">{{ $initialTheme['meta']['label'] }}</h2>
                    <p id="themeEditorDescription">{{ $initialTheme['meta']['description'] }}</p>
                </div>
                <button type="button" class="theme-reset" id="themeReset">
                    <i class="fa-solid fa-arrow-rotate-left"></i> Restaurar original
                </button>
            </div>

            <form id="themeForm" novalidate>
                <section class="theme-editor__section">
                    <div class="theme-editor__section-title">
                        <span class="theme-step">01</span>
                        <div><h3>Paleta de cores</h3><p>As alterações aparecem imediatamente na prévia e são salvas automaticamente.</p></div>
                    </div>
                    <div class="theme-color-grid">
                        @foreach($colorFields as $field => [$label, $help])
                            <label class="theme-color-field" for="theme-{{ $field }}">
                                <span class="theme-color-field__swatch">
                                    <input type="color" id="theme-{{ $field }}" data-theme-field="{{ $field }}" data-color-picker>
                                </span>
                                <span class="theme-color-field__copy"><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
                                <input type="text" class="theme-color-field__hex" data-color-hex="{{ $field }}" maxlength="7" spellcheck="false" aria-label="Código hexadecimal de {{ $label }}">
                            </label>
                        @endforeach
                    </div>
                    <div class="theme-contrast" id="themeContrast" role="status" aria-live="polite"></div>
                </section>

                <section class="theme-editor__section">
                    <div class="theme-editor__section-title">
                        <span class="theme-step">02</span>
                        <div><h3>Header e footer</h3><p>Controle a navegação e o rodapé sem interferir no layout de cada experiência.</p></div>
                    </div>
                    <div class="theme-color-grid">
                        @foreach($headerFooterFields as $field => [$label, $help])
                            <label class="theme-color-field" for="theme-{{ $field }}">
                                <span class="theme-color-field__swatch"><input type="color" id="theme-{{ $field }}" data-theme-field="{{ $field }}" data-color-picker></span>
                                <span class="theme-color-field__copy"><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
                                <input type="text" class="theme-color-field__hex" data-color-hex="{{ $field }}" maxlength="7" spellcheck="false" aria-label="Código hexadecimal de {{ $label }}">
                            </label>
                        @endforeach
                    </div>
                </section>

                <section class="theme-editor__section">
                    <div class="theme-editor__section-title">
                        <span class="theme-step">03</span>
                        <div><h3>Tipografia</h3><p>“Original da página” mantém exatamente as fontes anteriores de cada componente.</p></div>
                    </div>
                    <div class="theme-type-grid">
                        <label><span>Fonte dos títulos</span>
                            <select class="form-select" data-theme-field="heading_font">
                                @foreach($fontOptions as $key => $font)<option value="{{ $key }}">{{ $font['label'] }}</option>@endforeach
                            </select>
                        </label>
                        <label><span>Fonte dos textos</span>
                            <select class="form-select" data-theme-field="body_font">
                                @foreach($fontOptions as $key => $font)<option value="{{ $key }}">{{ $font['label'] }}</option>@endforeach
                            </select>
                        </label>
                        <label><span>Fonte do header</span>
                            <select class="form-select" data-theme-field="header_font">
                                @foreach($fontOptions as $key => $font)<option value="{{ $key }}">{{ $font['label'] }}</option>@endforeach
                            </select>
                        </label>
                        <label><span>Fonte do footer</span>
                            <select class="form-select" data-theme-field="footer_font">
                                @foreach($fontOptions as $key => $font)<option value="{{ $key }}">{{ $font['label'] }}</option>@endforeach
                            </select>
                        </label>
                        <label><span>Peso dos títulos</span>
                            <select class="form-select" data-theme-field="heading_weight">
                                <option value="original">Original da página</option>
                                @foreach(['400' => 'Regular', '500' => 'Médio', '600' => 'Semibold', '700' => 'Bold', '800' => 'Extra bold', '900' => 'Black'] as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label><span>Tamanho base</span>
                            <select class="form-select" data-theme-field="base_font_size" id="themeBaseSize">
                                <option value="original">Original da página</option>
                                @foreach(range(13, 20) as $size)<option value="{{ $size }}">{{ $size }}px</option>@endforeach
                            </select>
                        </label>
                    </div>
                </section>

                <section class="theme-editor__section">
                    <div class="theme-editor__section-title">
                        <span class="theme-step">04</span>
                        <div><h3>Componentes e interações</h3><p>Ajustes opcionais para bordas, hovers, botões e cards.</p></div>
                    </div>
                    <div class="theme-color-grid theme-color-grid--compact">
                        @foreach($interfaceColorFields as $field => [$label, $help])
                            <label class="theme-color-field" for="theme-{{ $field }}">
                                <span class="theme-color-field__swatch"><input type="color" id="theme-{{ $field }}" data-theme-field="{{ $field }}" data-color-picker></span>
                                <span class="theme-color-field__copy"><strong>{{ $label }}</strong><small>{{ $help }}</small></span>
                                <input type="text" class="theme-color-field__hex" data-color-hex="{{ $field }}" maxlength="7" spellcheck="false" aria-label="Código hexadecimal de {{ $label }}">
                            </label>
                        @endforeach
                    </div>
                    <div class="theme-type-grid theme-type-grid--spaced">
                        <label><span>Arredondamento dos botões</span>
                            <select class="form-select" data-theme-field="button_radius">
                                <option value="original">Original da página</option><option value="0">Reto</option><option value="4">4px</option><option value="8">8px</option><option value="12">12px</option><option value="20">20px</option><option value="999">Pílula</option>
                            </select>
                        </label>
                        <label><span>Arredondamento dos cards</span>
                            <select class="form-select" data-theme-field="card_radius">
                                <option value="original">Original da página</option><option value="0">Reto</option><option value="4">4px</option><option value="8">8px</option><option value="12">12px</option><option value="16">16px</option><option value="24">24px</option>
                            </select>
                        </label>
                    </div>
                </section>

                <div class="theme-editor__footer">
                    <p><i class="fa-solid fa-bolt"></i> Salvamento automático com cache atualizado instantaneamente.</p>
                    <button type="submit" class="theme-save-button" id="themeSaveButton">
                        <i class="fa-solid fa-floppy-disk"></i> Salvar agora
                    </button>
                </div>
            </form>
        </main>

        <aside class="theme-live-preview" id="themeLivePreview">
            <div class="theme-live-preview__bar">
                <span><i></i><i></i><i></i></span><small>Prévia de identidade</small>
            </div>
            <div class="theme-preview-canvas">
                <div class="theme-preview-nav"><strong>SAX</strong><span>Início &nbsp; Loja &nbsp; Contato</span></div>
                <div class="theme-preview-hero">
                    <span class="theme-preview-eyebrow">Experiência exclusiva</span>
                    <h3>Elegância em cada detalhe.</h3>
                    <p>Uma prévia rápida das cores, fontes, títulos e descrições selecionadas.</p>
                    <button type="button">Descobrir</button>
                </div>
                <div class="theme-preview-surface">
                    <span></span><div><strong>Conteúdo em destaque</strong><p>Superfícies e textos permanecem legíveis.</p><a href="#">Ver detalhes →</a></div>
                </div>
                <div class="theme-preview-footer"><strong>SAX</strong><div><b>Atendimento</b><span>Contato · Horários · Redes sociais</span></div></div>
            </div>
            <p class="theme-live-preview__note"><i class="fa-solid fa-circle-info"></i> A prévia representa a identidade. O layout original de cada página é preservado.</p>
        </aside>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('js/theme-admin.js') }}?v={{ filemtime(public_path('js/theme-admin.js')) }}"></script>
@endpush
@endsection
